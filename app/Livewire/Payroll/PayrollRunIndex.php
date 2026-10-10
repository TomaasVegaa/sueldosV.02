<?php

namespace App\Livewire\Payroll;

use App\Domain\Payroll\Services\PayrollSettlementService;
use App\Domain\Tenant\EmployerContext;
use App\Models\AuditEvent;
use App\Models\Employment;
use App\Models\PayrollEmployeeInput;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Support\Authorization\AuthorizesPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PayrollRunIndex extends Component
{
    use AuthorizesPermissions;

    // Filter / Selection
    public ?int $selectedPeriodId = null;

    public ?int $selectedRunId = null;

    // New Run Modal
    public bool $showNewRunModal = false;

    public string $new_year_month = '';

    public string $new_run_type = 'M';

    public int $new_run_number = 1;

    public string $new_pay_date = '';

    public int $new_base_days = 30;

    public string $new_notes = '';

    // Novelties Modal
    public bool $showNoveltiesModal = false;

    /** @var array<int, array{worked_days: int, absence_days: int, overtime_50_hours: string, overtime_100_hours: string, notes: string}> */
    public array $novelties = [];

    // Payslip Inspection Modal
    public bool $showPayslipModal = false;

    public ?int $inspectingPayslipId = null;

    // Toast / Feedback
    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->authorizeAnyPermission('payroll.view');

        $this->new_year_month = now()->format('Ym');
        $this->new_pay_date = now()->endOfMonth()->toDateString();

        $activeEmployerId = EmployerContext::getActiveEmployerId();
        if ($activeEmployerId) {
            $latestPeriod = PayrollPeriod::where('employer_id', $activeEmployerId)
                ->orderByDesc('year_month')
                ->first();
            if ($latestPeriod) {
                $this->selectedPeriodId = $latestPeriod->id;
                $latestRun = $latestPeriod->runs()->orderByDesc('id')->first();
                if ($latestRun) {
                    $this->selectedRunId = $latestRun->id;
                }
            }
        }
    }

    public function selectPeriod(int $periodId): void
    {
        $period = PayrollPeriod::findOrFail($periodId);
        $this->authorizeEmployerPermission('payroll.view', $period->employer_id);
        $this->selectedPeriodId = $periodId;
        $latestRun = $period->runs()->orderByDesc('id')->first();
        $this->selectedRunId = $latestRun?->id;
    }

    public function selectRun(int $runId): void
    {
        $run = PayrollRun::findOrFail($runId);
        $this->authorizeEmployerPermission('payroll.view', $run->employer_id);
        $this->selectedRunId = $runId;
    }

    public function openNewRunModal(): void
    {
        $this->authorizeEmployerPermission('payroll.calculate');
        $this->resetErrorBag();
        $this->new_year_month = now()->format('Ym');
        $this->new_run_type = 'M';
        $this->new_run_number = 1;
        $this->new_pay_date = now()->endOfMonth()->toDateString();
        $this->new_base_days = 30;
        $this->new_notes = '';
        $this->showNewRunModal = true;
    }

    public function createRun(): void
    {
        $employerId = EmployerContext::getActiveEmployerId();
        $this->authorizeEmployerPermission('payroll.calculate', $employerId);

        $this->validate([
            'new_year_month' => ['required', 'regex:/^\d{4}(0[1-9]|1[0-2])$/'],
            'new_run_type' => ['required', 'in:M,Q1,Q2,SAC1,SAC2,VAC,FIN'],
            'new_pay_date' => ['required', 'date'],
            'new_base_days' => ['required', 'integer', 'min:1', 'max:31'],
        ]);

        $period = PayrollPeriod::firstOrCreate(
            ['employer_id' => $employerId, 'year_month' => (int) $this->new_year_month],
            ['status' => 'OPEN']
        );

        // Auto-increment run number if already exists
        $existingCount = PayrollRun::where('employer_id', $employerId)
            ->where('payroll_period_id', $period->id)
            ->where('run_type', $this->new_run_type)
            ->count();
        $runNumber = $existingCount + 1;

        $run = PayrollRun::create([
            'employer_id' => $employerId,
            'payroll_period_id' => $period->id,
            'run_type' => $this->new_run_type,
            'run_number' => $runNumber,
            'pay_date' => $this->new_pay_date,
            'base_days' => $this->new_base_days,
            'status' => 'DRAFT',
            'notes' => $this->new_notes ?: null,
        ]);

        AuditEvent::log(
            'PAYROLL_RUN_CREATED',
            "Se creó corrida de liquidación {$run->run_type_label} N.° {$run->run_number} para período {$period->formatted_period}",
            $run
        );

        $this->selectedPeriodId = $period->id;
        $this->selectedRunId = $run->id;
        $this->showNewRunModal = false;
        $this->successMessage = 'Corrida de liquidación creada exitosamente.';
    }

    public function openNoveltiesModal(int $runId): void
    {
        $run = PayrollRun::findOrFail($runId);
        $this->authorizeEmployerPermission('payroll.calculate', $run->employer_id);
        $this->selectedRunId = $runId;
        $employerId = $run->employer_id;
        $yearMonth = $run->period->year_month;

        $employments = Employment::where('employer_id', $employerId)
            ->where('status', 'ACTIVE')
            ->with(['worker', 'latestVersion'])
            ->get();

        $existingInputs = PayrollEmployeeInput::where('employer_id', $employerId)
            ->where('year_month', $yearMonth)
            ->get()
            ->keyBy('employment_id');

        $this->novelties = [];
        foreach ($employments as $emp) {
            $input = $existingInputs->get($emp->id);
            $this->novelties[$emp->id] = [
                'worked_days' => $input ? $input->worked_days : $run->base_days,
                'absence_days' => $input ? $input->absence_days : 0,
                'overtime_50_hours' => $input ? (string) $input->overtime_50_hours : '0.00',
                'overtime_100_hours' => $input ? (string) $input->overtime_100_hours : '0.00',
                'notes' => $input ? ($input->notes ?? '') : '',
            ];
        }

        $this->showNoveltiesModal = true;
    }

    public function saveNovelties(): void
    {
        $run = PayrollRun::findOrFail($this->selectedRunId);
        $employerId = $run->employer_id;
        $yearMonth = $run->period->year_month;
        $this->authorizeEmployerPermission('payroll.calculate', $employerId);

        $validated = $this->validate([
            'novelties' => ['array'],
            'novelties.*.worked_days' => ['required', 'integer', 'min:0', 'max:31'],
            'novelties.*.absence_days' => ['required', 'integer', 'min:0', 'max:31'],
            'novelties.*.overtime_50_hours' => ['required', 'decimal:0,2', 'min:0'],
            'novelties.*.overtime_100_hours' => ['required', 'decimal:0,2', 'min:0'],
            'novelties.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employmentIds = array_map('intval', array_keys($validated['novelties']));
        $validEmploymentIds = Employment::where('employer_id', $employerId)
            ->whereIn('id', $employmentIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        abort_unless(count($employmentIds) === count($validEmploymentIds), 422, 'Hay legajos ajenos a la empresa activa.');

        DB::transaction(function () use ($validated, $employerId, $yearMonth): void {
            foreach ($validated['novelties'] as $employmentId => $data) {
                PayrollEmployeeInput::updateOrCreate(
                    [
                        'employer_id' => $employerId,
                        'employment_id' => (int) $employmentId,
                        'year_month' => $yearMonth,
                    ],
                    [
                        'worked_days' => (int) $data['worked_days'],
                        'absence_days' => (int) $data['absence_days'],
                        'overtime_50_hours' => (string) $data['overtime_50_hours'],
                        'overtime_100_hours' => (string) $data['overtime_100_hours'],
                        'notes' => $data['notes'] ?: null,
                    ]
                );
            }
        });

        $this->showNoveltiesModal = false;
        $this->successMessage = 'Novedades y días trabajados guardados correctamente.';
    }

    public function calculateRun(int $runId): void
    {
        $run = PayrollRun::findOrFail($runId);
        $this->authorizeEmployerPermission('payroll.calculate', $run->employer_id);
        $service = new PayrollSettlementService;

        try {
            $version = $service->calculateRun($run, Auth::id());

            AuditEvent::log(
                'PAYROLL_RUN_CALCULATED',
                "Se calculó la versión v{$version->version_number} para {$run->run_type_label} (Hash: ".substr($version->snapshot_sha256, 0, 10).'..., Recibos: '.$version->payslips()->count().')',
                $version
            );

            $this->successMessage = "Simulación calculada. Versión técnica v{$version->version_number} generada con hash SHA-256; pendiente de validación profesional.";
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'No se pudo completar la simulación. Revise los datos o consulte el registro técnico.';
        }
    }

    public function inspectPayslip(int $payslipId): void
    {
        $payslip = Payslip::with('runVersion.run')->findOrFail($payslipId);
        abort_unless($payslip->runVersion?->run, 404);
        $this->authorizeEmployerPermission('payroll.view', $payslip->runVersion->run->employer_id);
        $this->inspectingPayslipId = $payslipId;
        $this->showPayslipModal = true;
    }

    public function closePayslipModal(): void
    {
        $this->showPayslipModal = false;
        $this->inspectingPayslipId = null;
    }

    public function render()
    {
        $this->authorizeAnyPermission('payroll.view');
        $employerId = EmployerContext::getActiveEmployerId();

        $periods = $employerId
            ? PayrollPeriod::where('employer_id', $employerId)->orderByDesc('year_month')->get()
            : collect();

        $currentPeriod = $this->selectedPeriodId
            ? PayrollPeriod::find($this->selectedPeriodId)
            : $periods->first();

        $runs = $currentPeriod
            ? $currentPeriod->runs()->orderByDesc('run_number')->with(['currentVersion.payslips'])->get()
            : collect();

        $currentRun = $this->selectedRunId
            ? PayrollRun::find($this->selectedRunId)
            : $runs->first();

        $currentVersion = $currentRun?->currentVersion;
        $payslips = $currentVersion
            ? $currentVersion->payslips()->with(['employment.worker', 'lines'])->get()
            : collect();

        $inspectingSlip = $this->inspectingPayslipId
            ? Payslip::with(['employment.worker', 'employment.employer', 'lines', 'bases', 'runVersion.run.period'])->find($this->inspectingPayslipId)
            : null;
        if ($inspectingSlip) {
            abort_unless($inspectingSlip->runVersion?->run, 404);
            $this->authorizeEmployerPermission('payroll.view', $inspectingSlip->runVersion->run->employer_id);
        }

        // Active workforce for run summary
        $activeEmployeesCount = $employerId
            ? Employment::where('employer_id', $employerId)->where('status', 'ACTIVE')->count()
            : 0;

        return view('livewire.payroll.payroll-run-index', [
            'periods' => $periods,
            'currentPeriod' => $currentPeriod,
            'runs' => $runs,
            'currentRun' => $currentRun,
            'currentVersion' => $currentVersion,
            'payslips' => $payslips,
            'inspectingSlip' => $inspectingSlip,
            'activeEmployeesCount' => $activeEmployeesCount,
            'canCalculate' => config('payroll.unvalidated_simulation_enabled')
                && $employerId !== null
                && Auth::user()?->hasPermission('payroll.calculate', $employerId),
            'canExport' => $employerId !== null && Auth::user()?->hasPermission('payroll.export', $employerId),
            'simulationEnabled' => config('payroll.unvalidated_simulation_enabled'),
        ])->layout('components.layouts.app', ['title' => 'Liquidaciones y Recibos — Sueldos Web']);
    }
}
