<?php

namespace Tests\Feature;

use App\Domain\Payroll\Services\PayrollSettlementService;
use App\Models\Employer;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use LogicException;
use Tests\TestCase;

class PayrollSafetyGateTest extends TestCase
{
    public function test_unvalidated_payroll_calculation_is_disabled_by_configuration(): void
    {
        config()->set('payroll.unvalidated_simulation_enabled', false);
        $employer = Employer::firstOrFail();
        $period = PayrollPeriod::withoutGlobalScopes()->create([
            'employer_id' => $employer->id,
            'year_month' => 202610,
            'status' => 'OPEN',
        ]);
        $run = PayrollRun::withoutGlobalScopes()->create([
            'employer_id' => $employer->id,
            'payroll_period_id' => $period->id,
            'run_type' => 'M',
            'run_number' => 1,
            'pay_date' => '2026-10-31',
            'base_days' => 30,
            'status' => 'DRAFT',
        ]);

        try {
            (new PayrollSettlementService)->calculateRun($run);
            $this->fail('El servicio no debe calcular con la simulación deshabilitada.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('deshabilitada', $exception->getMessage());
        }

        $this->assertDatabaseCount('payroll_run_versions', 0);
    }
}
