<?php

namespace App\Livewire\Payroll;

use App\Domain\Payroll\Services\PayrollSettlementService;
use App\Domain\Tenant\EmployerContext;
use App\Models\AuditEvent;
use App\Models\Employment;
use App\Models\PayrollEmployeeInput;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\PayrollRunVersion;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PayrollRunIndex extends Component
{
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
    /** @var array<int, array{worked_days: int, absence_days: int, overtime_50_hours: float, overtime_100_hours: float, notes: string}> */
    public array $novelties = [];

    // Payslip Inspection Modal
    public bool $showPayslipModal = false;
    public ?int $inspectingPayslipId = null;

    // Toast / Feedback
    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    public function mount(): void
    {
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
        $this->selectedPeriodId = $periodId;
        $period = PayrollPeriod::find($periodId);
        $latestRun = $period?->runs()->orderByDesc('id')->first();
        $this->selectedRunId = $latestRun?->id;
    }

    public function selectRun(int $runId): void
    {
        $this->selectedRunId = $runId;
    }

    public function openNewRunModal(): void
    {
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
        $this->validate([
            'new_year_month' => ['required', 'digits:6'],
            'new_run_type' => ['required', 'in:M,Q1,Q2,SAC1,SAC2,VAC,FIN'],
            'new_pay_date' => ['required', 'date'],
            'new_base_days' => ['required', 'integer', 'min:1', 'max:31'],
        ]);

        $employerId = EmployerContext::getActiveEmployerId();
        if (!$employerId) {
            $this->errorMessage = 'Debe seleccionar una empresa activa en el encabezado.';
            return;
        }

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
        $this->successMessage = "Corrida de liquidación creada exitosamente.";
    }

    public function openNoveltiesModal(int $runId): void
    {
        $this->selectedRunId = $runId;
        $run = PayrollRun::findOrFail($runId);
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
                'overtime_50_hours' => $input ? (float) $input->overtime_50_hours : 0.0,
                'overtime_100_hours' => $input ? (float) $input->overtime_100_hours : 0.0,
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

        foreach ($this->novelties as $employmentId => $data) {
            PayrollEmployeeInput::updateOrCreate(
                [
                    'employer_id' => $employerId,
                    'employment_id' => $employmentId,
                    'year_month' => $yearMonth,
                ],
                [
                    'worked_days' => (int) ($data['worked_days'] ?? 30),
                    'absence_days' => (int) ($data['absence_days'] ?? 0),
                    'overtime_50_hours' => (float) ($data['overtime_50_hours'] ?? 0.0),
                    'overtime_100_hours' => (float) ($data['overtime_100_hours'] ?? 0.0),
                    'notes' => $data['notes'] ?: null,
                ]
            );
        }

        $this->showNoveltiesModal = false;
        $this->successMessage = "Novedades y días trabajados guardados correctamente.";
    }

    public function calculateRun(int $runId): void
    {
        $run = PayrollRun::findOrFail($runId);
        $service = new PayrollSettlementService();

        try {
            $version = $service->calculateRun($run, Auth::id());

            AuditEvent::log(
                'PAYROLL_RUN_CALCULATED',
                "Se calculó la versión v{$version->version_number} para {$run->run_type_label} (Hash: " . substr($version->snapshot_sha256, 0, 10) . "..., Recibos: " . $version->payslips()->count() . ")",
                $version
            );

            $this->successMessage = "¡Liquidación calculada con éxito! Versión v{$version->version_number} generada inmutable con hash SHA-256.";
        } catch (\Throwable $e) {
            $this->errorMessage = "Error en el cálculo salarial: " . $e->getMessage();
        }
    }

    public function inspectPayslip(int $payslipId): void
    {
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
        ]);
    }
}
