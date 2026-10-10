<?php

namespace Tests\Feature;

use App\Domain\Tenant\EmployerContext;
use App\Livewire\Payroll\PayrollRunIndex;
use App\Models\Employer;
use App\Models\Employment;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\PayrollRunVersion;
use App\Models\Payslip;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class PayrollAuthorizationTest extends TestCase
{
    public function test_user_without_payroll_permission_cannot_open_payroll_page(): void
    {
        $user = User::factory()->create(['status' => 'ACTIVE']);

        $this->actingAs($user)
            ->get(route('payroll.index'))
            ->assertForbidden();
    }

    public function test_auditor_cannot_open_calculation_actions(): void
    {
        $auditor = $this->createUserWithRole('auditor-payroll@test.invalid', 'AUDITOR');

        Livewire::actingAs($auditor)
            ->test(PayrollRunIndex::class)
            ->assertOk()
            ->assertSee('SIMULACIÓN NO VALIDADA')
            ->call('openNewRunModal')
            ->assertForbidden();
    }

    public function test_user_without_export_permission_cannot_download_payslip(): void
    {
        $auditor = $this->createUserWithRole('auditor-pdf@test.invalid', 'AUDITOR');
        $payslip = $this->createPayslipFor(Employer::firstOrFail());

        $this->actingAs($auditor)
            ->get(route('payroll.payslip.pdf', $payslip))
            ->assertForbidden();
    }

    public function test_employer_scoped_exporter_cannot_download_another_employers_payslip(): void
    {
        $employers = Employer::orderBy('id')->get();
        $allowedEmployer = $employers->firstOrFail();
        $forbiddenEmployer = $employers->skip(1)->firstOrFail();
        $reviewer = $this->createUserWithRole(
            'reviewer-scoped@test.invalid',
            'REVISOR',
            $allowedEmployer->id
        );
        $payslip = $this->createPayslipFor($forbiddenEmployer);

        $this->actingAs($reviewer)
            ->withSession(['active_employer_id' => $allowedEmployer->id])
            ->get(route('payroll.payslip.download', $payslip))
            ->assertForbidden();
    }

    public function test_scoped_calculator_cannot_save_novelties_for_another_employers_employment(): void
    {
        $employers = Employer::orderBy('id')->get();
        $allowedEmployer = $employers->firstOrFail();
        $forbiddenEmployer = $employers->skip(1)->firstOrFail();
        $calculator = $this->createUserWithRole(
            'calculator-scoped@test.invalid',
            'LIQUIDADOR',
            $allowedEmployer->id
        );
        $foreignEmployment = Employment::withoutGlobalScopes()
            ->where('employer_id', $forbiddenEmployer->id)
            ->firstOrFail();
        $run = $this->createRunFor($allowedEmployer);

        $this->actingAs($calculator);
        EmployerContext::setActiveEmployerId($allowedEmployer->id);

        Livewire::actingAs($calculator)
            ->test(PayrollRunIndex::class)
            ->set('selectedRunId', $run->id)
            ->set('novelties', [
                $foreignEmployment->id => [
                    'worked_days' => 30,
                    'absence_days' => 0,
                    'overtime_50_hours' => '0.00',
                    'overtime_100_hours' => '0.00',
                    'notes' => '',
                ],
            ])
            ->call('saveNovelties')
            ->assertStatus(422);

        $this->assertDatabaseMissing('payroll_employee_inputs', [
            'employer_id' => $allowedEmployer->id,
            'employment_id' => $foreignEmployment->id,
        ]);
    }

    public function test_export_rejects_a_payslip_with_inconsistent_employer_relations(): void
    {
        $employers = Employer::orderBy('id')->get();
        $allowedEmployer = $employers->firstOrFail();
        $otherEmployer = $employers->skip(1)->firstOrFail();
        $reviewer = $this->createUserWithRole(
            'reviewer-integrity@test.invalid',
            'REVISOR',
            $allowedEmployer->id
        );
        $foreignEmployment = Employment::withoutGlobalScopes()
            ->where('employer_id', $otherEmployer->id)
            ->firstOrFail();
        $run = $this->createRunFor($allowedEmployer);
        $version = PayrollRunVersion::create([
            'payroll_run_id' => $run->id,
            'version_number' => 1,
            'status' => 'CURRENT',
        ]);
        $payslip = Payslip::create([
            'payroll_run_version_id' => $version->id,
            'employment_id' => $foreignEmployment->id,
            'file_number' => $foreignEmployment->file_number,
            'worker_name' => 'Relación inconsistente',
            'worker_cuil' => '00000000000',
            'category_code' => 'TEST',
            'category_name' => 'Categoría de prueba',
            'hire_date' => '2026-01-01',
        ]);

        $this->actingAs($reviewer)
            ->withSession(['active_employer_id' => $allowedEmployer->id])
            ->get(route('payroll.payslip.pdf', $payslip))
            ->assertNotFound();
    }

    private function createUserWithRole(string $email, string $roleCode, ?int $employerId = null): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'status' => 'ACTIVE',
        ]);
        $role = Role::where('code', $roleCode)->firstOrFail();
        $user->roles()->attach($role->id, ['employer_id' => $employerId]);

        return $user;
    }

    private function createPayslipFor(Employer $employer): Payslip
    {
        $employment = Employment::withoutGlobalScopes()
            ->where('employer_id', $employer->id)
            ->firstOrFail();
        $run = $this->createRunFor($employer);
        $version = PayrollRunVersion::create([
            'payroll_run_id' => $run->id,
            'version_number' => 1,
            'status' => 'CURRENT',
        ]);

        return Payslip::create([
            'payroll_run_version_id' => $version->id,
            'employment_id' => $employment->id,
            'file_number' => $employment->file_number,
            'worker_name' => 'Persona de prueba',
            'worker_cuil' => '00000000000',
            'category_code' => 'TEST',
            'category_name' => 'Categoría de prueba',
            'hire_date' => '2026-01-01',
        ]);
    }

    private function createRunFor(Employer $employer): PayrollRun
    {
        $period = PayrollPeriod::withoutGlobalScopes()->create([
            'employer_id' => $employer->id,
            'year_month' => 202610,
            'status' => 'OPEN',
        ]);

        return PayrollRun::withoutGlobalScopes()->create([
            'employer_id' => $employer->id,
            'payroll_period_id' => $period->id,
            'run_type' => 'M',
            'run_number' => 1,
            'pay_date' => '2026-10-31',
            'base_days' => 30,
            'status' => 'DRAFT',
        ]);
    }
}
