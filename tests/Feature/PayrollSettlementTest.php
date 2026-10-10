<?php

namespace Tests\Feature;

use App\Domain\Payroll\Services\PayrollSettlementService;
use App\Domain\Payroll\Services\PayslipPdfService;
use App\Models\Agreement;
use App\Models\AgreementCategory;
use App\Models\CategoryScaleVersion;
use App\Models\Employer;
use App\Models\Employment;
use App\Models\EmploymentVersion;
use App\Models\PayrollConcept;
use App\Models\PayrollEmployeeInput;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PayrollSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected Employer $employer;
    protected Agreement $agreement;
    protected AgreementCategory $category;
    protected Employment $employment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndUsersSeeder::class);
        $this->seed(\Database\Seeders\AgreementsAndScalesSeeder::class);
        $this->seed(\Database\Seeders\GlobalConceptsSeeder::class);
        $this->seed(\Database\Seeders\EmployersAndWorkforceSeeder::class);

        $this->operator = User::where('email', 'admin@estudio.test')->first();
        $this->employer = Employer::first();
        $this->employment = Employment::where('employer_id', $this->employer->id)->first();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function payroll_page_can_be_rendered_for_authenticated_operator(): void
    {
        $response = $this->actingAs($this->operator)
            ->withSession(['active_employer_id' => $this->employer->id])
            ->get(route('payroll.index'));

        $response->assertStatus(200);
        $response->assertSee('Liquidaciones y Recibos');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function settlement_service_calculates_comercio_scale_with_exact_amounts(): void
    {
        $period = PayrollPeriod::create([
            'employer_id' => $this->employer->id,
            'year_month' => 202609,
            'status' => 'OPEN',
        ]);

        $run = PayrollRun::create([
            'employer_id' => $this->employer->id,
            'payroll_period_id' => $period->id,
            'run_type' => 'M',
            'run_number' => 1,
            'pay_date' => '2026-09-30',
            'base_days' => 30,
            'status' => 'DRAFT',
        ]);

        $service = new PayrollSettlementService();
        $runVersion = $service->calculateRun($run, $this->operator->id);

        $this->assertEquals(1, $runVersion->version_number);
        $this->assertEquals('CURRENT', $runVersion->status);
        $this->assertNotEmpty($runVersion->snapshot_sha256);

        $payslip = $runVersion->payslips()->first();
        $this->assertNotNull($payslip);
        $this->assertEquals('0001', $payslip->file_number);
        $this->assertEquals(4, $payslip->seniority_years); // 2022-03 to 2026-09

        // Vendedor B Media Jornada (1/2):
        // Básico 50%: 613.174,50
        // Antigüedad (4 años * 1%): 24.526,98
        // Presentismo (1/12): 53.141,79
        // Total Remunerativo: 690.843,27
        $this->assertEquals('690843.27', (string) $payslip->gross_remunerative);

        // No Remunerativo 50%: 60.000,00
        // Antigüedad NR: 2.400,00
        // Presentismo NR (62.400 / 12): 5.200,00
        // Total No Remunerativo: 67.600,00
        $this->assertEquals('67600.00', (string) $payslip->gross_non_remunerative);

        // Net salary must be exactly $ 614.354,39 and formatted in words
        $this->assertEquals('614354.39', (string) $payslip->net_salary);
        $this->assertStringContainsString('SON PESOS:', $payslip->net_salary_words);

        // Bases ARCA must be created
        $this->assertDatabaseHas('payslip_bases', [
            'payslip_id' => $payslip->id,
            'base_code' => 'BASE_1',
            'amount' => '690843.27',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function absences_invalidate_presentismo_due_to_condition(): void
    {
        $period = PayrollPeriod::create([
            'employer_id' => $this->employer->id,
            'year_month' => 202609,
            'status' => 'OPEN',
        ]);

        $run = PayrollRun::create([
            'employer_id' => $this->employer->id,
            'payroll_period_id' => $period->id,
            'run_type' => 'M',
            'run_number' => 1,
            'pay_date' => '2026-09-30',
            'base_days' => 30,
            'status' => 'DRAFT',
        ]);

        // Employee had 2 absences
        PayrollEmployeeInput::create([
            'employer_id' => $this->employer->id,
            'employment_id' => $this->employment->id,
            'year_month' => 202609,
            'worked_days' => 28,
            'absence_days' => 2,
        ]);

        $service = new PayrollSettlementService();
        $runVersion = $service->calculateRun($run);
        $payslip = $runVersion->payslips()->first();

        // Presentismo (concept 250) has condition CQ004<0.5.
        // Because absence_days is 2, CQ004 is 2, so 2 < 0.5 is FALSE.
        // Presentismo concept 250 should NOT be present in lines!
        $presentismoLine = $payslip->lines()->where('concept_code', '250')->first();
        $this->assertNull($presentismoLine);

        // Inasistencias concept 004 SHOULD be present in lines
        $inasistenciasLine = $payslip->lines()->where('concept_code', '004')->first();
        $this->assertNotNull($inasistenciasLine);
        $this->assertEquals(2, (float) $inasistenciasLine->quantity);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function recalculation_generates_new_version_with_inmutable_snapshot(): void
    {
        $period = PayrollPeriod::create([
            'employer_id' => $this->employer->id,
            'year_month' => 202609,
            'status' => 'OPEN',
        ]);

        $run = PayrollRun::create([
            'employer_id' => $this->employer->id,
            'payroll_period_id' => $period->id,
            'run_type' => 'M',
            'run_number' => 1,
            'pay_date' => '2026-09-30',
            'base_days' => 30,
            'status' => 'DRAFT',
        ]);

        $service = new PayrollSettlementService();
        $v1 = $service->calculateRun($run);
        $this->assertEquals(1, $v1->version_number);
        $this->assertEquals('CURRENT', $v1->status);

        $v2 = $service->calculateRun($run);
        $this->assertEquals(2, $v2->version_number);
        $this->assertEquals('CURRENT', $v2->status);

        $v1->refresh();
        $this->assertEquals('REPLACED_BY_CORRECTION', $v1->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function payslip_pdf_can_be_streamed_via_http(): void
    {
        $period = PayrollPeriod::create([
            'employer_id' => $this->employer->id,
            'year_month' => 202609,
            'status' => 'OPEN',
        ]);

        $run = PayrollRun::create([
            'employer_id' => $this->employer->id,
            'payroll_period_id' => $period->id,
            'run_type' => 'M',
            'run_number' => 1,
            'pay_date' => '2026-09-30',
            'base_days' => 30,
            'status' => 'DRAFT',
        ]);

        $service = new PayrollSettlementService();
        $runVersion = $service->calculateRun($run);
        $payslip = $runVersion->payslips()->first();

        $response = $this->actingAs($this->operator)
            ->get(route('payroll.payslip.pdf', $payslip->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
