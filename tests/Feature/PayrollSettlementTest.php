<?php

namespace Tests\Feature;

use App\Domain\Payroll\Services\PayrollSettlementService;
use App\Models\Employer;
use App\Models\Employment;
use App\Models\PayrollConcept;
use App\Models\PayrollEmployeeInput;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\User;
use Tests\TestCase;

class PayrollSettlementTest extends TestCase
{
    protected User $operator;

    protected Employer $employer;

    protected Employment $employment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::where('email', 'admin@estudio.test')->firstOrFail();
        $this->employer = Employer::firstOrFail();
        $this->employment = Employment::withoutGlobalScopes()
            ->where('employer_id', $this->employer->id)
            ->firstOrFail();

        // The seeded Comercio catalog is intentionally not used as an oracle:
        // its formulas and references still require professional validation.
        PayrollConcept::query()->update(['status' => 'INACTIVE']);
        PayrollConcept::create([
            'code' => 'TST_BASE',
            'name' => 'Concepto técnico ficticio',
            'type' => 'REMUNERATIVO',
            'formula' => '100.00',
            'is_automatic' => true,
            'order' => 10,
            'status' => 'ACTIVE',
        ]);
        PayrollConcept::create([
            'code' => 'TST_COND',
            'name' => 'Condición técnica ficticia',
            'type' => 'REMUNERATIVO',
            'formula' => '25.00',
            'liquidation_condition' => 'CQ004<0.5',
            'is_automatic' => true,
            'order' => 20,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_payroll_page_can_be_rendered_for_authenticated_operator(): void
    {
        $this->actingAs($this->operator)
            ->withSession(['active_employer_id' => $this->employer->id])
            ->get(route('payroll.index'))
            ->assertOk()
            ->assertSee('Liquidaciones y Recibos')
            ->assertSee('SIMULACIÓN NO VALIDADA');
    }

    public function test_settlement_service_calculates_an_explicit_synthetic_fixture_exactly(): void
    {
        $runVersion = (new PayrollSettlementService)->calculateRun(
            $this->createRun(),
            $this->operator->id
        );

        $this->assertSame(1, $runVersion->version_number);
        $this->assertSame('CURRENT', $runVersion->status);
        $this->assertNotEmpty($runVersion->snapshot_sha256);

        $payslip = $runVersion->payslips()->firstOrFail();
        $this->assertSame('125.00', (string) $payslip->gross_remunerative);
        $this->assertSame('0.00', (string) $payslip->gross_non_remunerative);
        $this->assertSame('125.00', (string) $payslip->net_salary);
        $this->assertStringContainsString('SON PESOS:', $payslip->net_salary_words);
        $this->assertDatabaseHas('payslip_bases', [
            'payslip_id' => $payslip->id,
            'base_code' => 'BASE_1',
            'amount' => '125.00',
        ]);
    }

    public function test_condition_excludes_the_synthetic_concept_when_false(): void
    {
        PayrollEmployeeInput::create([
            'employer_id' => $this->employer->id,
            'employment_id' => $this->employment->id,
            'year_month' => 202609,
            'worked_days' => 28,
            'absence_days' => 2,
        ]);

        $payslip = (new PayrollSettlementService)
            ->calculateRun($this->createRun())
            ->payslips()
            ->firstOrFail();

        $this->assertDatabaseHas('payslip_lines', [
            'payslip_id' => $payslip->id,
            'concept_code' => 'TST_BASE',
        ]);
        $this->assertDatabaseMissing('payslip_lines', [
            'payslip_id' => $payslip->id,
            'concept_code' => 'TST_COND',
        ]);
        $this->assertSame('100.00', (string) $payslip->gross_remunerative);
    }

    public function test_recalculation_generates_a_new_immutable_snapshot_version(): void
    {
        $run = $this->createRun();
        $service = new PayrollSettlementService;
        $v1 = $service->calculateRun($run);
        $v2 = $service->calculateRun($run);

        $this->assertSame(1, $v1->version_number);
        $this->assertSame(2, $v2->version_number);
        $this->assertSame('CURRENT', $v2->status);
        $this->assertSame('REPLACED_BY_CORRECTION', $v1->fresh()->status);
    }

    public function test_payslip_pdf_can_be_streamed_via_http(): void
    {
        $payslip = (new PayrollSettlementService)
            ->calculateRun($this->createRun())
            ->payslips()
            ->firstOrFail();

        $response = $this->actingAs($this->operator)
            ->get(route('payroll.payslip.pdf', $payslip));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    private function createRun(): PayrollRun
    {
        $period = PayrollPeriod::create([
            'employer_id' => $this->employer->id,
            'year_month' => 202609,
            'status' => 'OPEN',
        ]);

        return PayrollRun::create([
            'employer_id' => $this->employer->id,
            'payroll_period_id' => $period->id,
            'run_type' => 'M',
            'run_number' => 1,
            'pay_date' => '2026-09-30',
            'base_days' => 30,
            'status' => 'DRAFT',
        ]);
    }
}
