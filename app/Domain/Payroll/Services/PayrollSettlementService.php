<?php

namespace App\Domain\Payroll\Services;

use App\Domain\Payroll\Engine\DependencyGraph;
use App\Domain\Payroll\Engine\ExpressionEvaluator;
use App\Models\AgreementCategory;
use App\Models\Employment;
use App\Models\PayrollConcept;
use App\Models\PayrollEmployeeInput;
use App\Models\PayrollRun;
use App\Models\PayrollRunVersion;
use App\Models\Payslip;
use App\Models\PayslipBase;
use App\Models\PayslipLine;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

class PayrollSettlementService
{
    /**
     * Calculate and generate a new immutable version for an entire PayrollRun batch.
     */
    public function calculateRun(PayrollRun $run, ?int $userId = null): PayrollRunVersion
    {
        if (! config('payroll.unvalidated_simulation_enabled')) {
            throw new LogicException('La simulación de haberes está deshabilitada hasta validar reglas, fuentes y casos.');
        }

        return DB::transaction(function () use ($run, $userId) {
            // Find current version number and determine next version
            $latestVersion = $run->versions()->first();
            $nextVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

            if ($latestVersion) {
                $latestVersion->update(['status' => 'REPLACED_BY_CORRECTION']);
            }

            $runVersion = PayrollRunVersion::create([
                'payroll_run_id' => $run->id,
                'version_number' => $nextVersionNumber,
                'status' => 'CURRENT',
                'parent_version_id' => $latestVersion?->id,
                'calculated_by' => $userId,
                'calculated_at' => now(),
            ]);

            // Period year_month
            $period = $run->period()->withoutGlobalScopes()->first();
            $yearMonth = $period ? $period->year_month : (int) Carbon::parse($run->pay_date)->format('Ym');

            // Fetch active employments for the employer
            $employments = Employment::withoutGlobalScopes()
                ->where('employer_id', $run->employer_id)
                ->where('status', 'ACTIVE')
                ->where('hire_date', '<=', $run->pay_date)
                ->where(function ($q) use ($run) {
                    $q->whereNull('termination_date')
                        ->orWhere('termination_date', '>=', Carbon::parse($run->pay_date)->startOfMonth());
                })
                ->with(['worker', 'versions'])
                ->get();

            // Pre-load employee inputs for this period
            $inputs = PayrollEmployeeInput::withoutGlobalScopes()
                ->where('employer_id', $run->employer_id)
                ->where('year_month', $yearMonth)
                ->get()
                ->keyBy('employment_id');

            $payslipSnapshots = [];

            foreach ($employments as $employment) {
                $input = $inputs->get($employment->id);
                $payslip = $this->calculateEmploymentSettlement($employment, $run, $runVersion, $input);
                $payslipSnapshots[] = [
                    'payslip_id' => $payslip->id,
                    'file_number' => $payslip->file_number,
                    'cuil' => $payslip->worker_cuil,
                    'gross_remunerative' => (string) $payslip->gross_remunerative,
                    'gross_non_remunerative' => (string) $payslip->gross_non_remunerative,
                    'total_deductions' => (string) $payslip->total_deductions,
                    'net_salary' => (string) $payslip->net_salary,
                ];
            }

            // Snapshot JSON and SHA-256 hash for audit and immutability
            $snapshotJson = json_encode([
                'run_id' => $run->id,
                'employer_id' => $run->employer_id,
                'period' => $yearMonth,
                'run_type' => $run->run_type,
                'version_number' => $nextVersionNumber,
                'calculated_at' => now()->toIso8601String(),
                'payslips_count' => count($payslipSnapshots),
                'payslips' => $payslipSnapshots,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            $runVersion->update([
                'snapshot_json' => $snapshotJson,
                'snapshot_sha256' => hash('sha256', $snapshotJson),
            ]);

            $run->update([
                'status' => 'CALCULATED',
            ]);

            return $runVersion->load(['payslips.lines', 'payslips.bases']);
        });
    }

    /**
     * Calculate individual employment settlement and create Payslip with lines and bases.
     */
    public function calculateEmploymentSettlement(
        Employment $employment,
        PayrollRun $run,
        PayrollRunVersion $runVersion,
        ?PayrollEmployeeInput $input = null
    ): Payslip {
        $payDateStr = $run->pay_date->toDateString();
        $employmentVersion = $employment->getVersionOn($payDateStr) ?? $employment->latestVersion;

        if (! $employmentVersion) {
            throw new RuntimeException("El legajo {$employment->file_number} no posee una versión de condiciones laborales activa a la fecha {$payDateStr}.");
        }

        // Category and Scales
        $category = AgreementCategory::where('code', $employmentVersion->category_code)->first();
        $scale = $category?->getScaleOn($payDateStr) ?? $category?->latestScale;

        $baseBasic = $scale
            ? BigDecimal::of((string) $scale->basic_salary)
            : BigDecimal::of((string) ($employmentVersion->agreed_salary ?? '0.00'));

        $baseNonTaxable = $scale && $scale->non_taxable_salary
            ? BigDecimal::of((string) $scale->non_taxable_salary)
            : BigDecimal::zero();

        // Workday proration (1: full, 2: 3/4, 3: 1/2, 4: 1/4)
        $workdayFactor = match ($employmentVersion->workday_type) {
            2 => BigDecimal::of('0.75'),
            3 => BigDecimal::of('0.50'),
            4 => BigDecimal::of('0.25'),
            default => BigDecimal::of('1.00'),
        };

        $sb = $baseBasic->multipliedBy($workdayFactor);
        $nr = $baseNonTaxable->multipliedBy($workdayFactor);

        // Seniority (Antigüedad)
        $refDate = $employment->seniority_date ?? $employment->hire_date;
        $seniorityYears = (int) Carbon::parse($refDate)->diffInYears($run->pay_date);
        $an = BigDecimal::of((string) $seniorityYears);

        // Worked days and absences
        $baseDays = $run->base_days ?: 30;
        $workedDays = $input && $input->worked_days !== null ? $input->worked_days : $baseDays;
        $absenceDays = $input ? $input->absence_days : 0;
        $vi = BigDecimal::of((string) $workedDays);
        $cq004 = BigDecimal::of((string) $absenceDays);

        // Base Context
        $context = [
            'SB' => $sb,
            'NR' => $nr,
            'AN' => $an,
            'VI' => $vi,
            'DI' => $vi,
            'IN' => BigDecimal::of('1'),
            'CQ004' => $cq004,
            'OSO' => (string) ($employmentVersion->health_insurer_code ?? '0'),
            'TH' => BigDecimal::zero(),
            'TS' => BigDecimal::zero(),
            'TD' => BigDecimal::zero(),
        ];

        // Fetch candidate concepts
        $concepts = PayrollConcept::active()
            ->forAgreementOrGlobal($category?->agreement_id)
            ->orderBy('order')
            ->get();

        // A known but non-applicable concept resolves explicitly to zero. A
        // reference to a code outside this catalog still fails in the evaluator.
        foreach ($concepts as $knownConcept) {
            $context['LQ'.strtoupper($knownConcept->code)] = BigDecimal::zero();
        }

        $applicableConcepts = new Collection;
        $customValues = $input?->custom_informed_values ?? [];

        foreach ($concepts as $concept) {
            $code = $concept->code;

            // Determine if concept should trigger
            $shouldInclude = false;

            if ($concept->is_automatic) {
                $shouldInclude = true;
            } elseif ($code === '004' && $absenceDays > 0) {
                // Inasistencias
                $shouldInclude = true;
            } elseif ($code === '012' && $input && BigDecimal::of((string) $input->overtime_50_hours)->isPositive()) {
                // Horas Extras 50%
                $shouldInclude = true;
            } elseif ($code === '013' && $input && BigDecimal::of((string) $input->overtime_100_hours)->isPositive()) {
                // Horas Extras 100%
                $shouldInclude = true;
            } elseif (isset($customValues[$code]) || isset($customValues['VI_'.$code]) || isset($customValues['IM_'.$code])) {
                $shouldInclude = true;
            }

            if (! $shouldInclude) {
                continue;
            }

            // Check liquidation condition (e.g. CQ004<0.5, OSO=126205)
            if (! empty($concept->liquidation_condition) && strtoupper($concept->liquidation_condition) !== 'NONE') {
                if (! $this->evaluateCondition($concept->liquidation_condition, $context)) {
                    continue;
                }
            }

            $applicableConcepts->push($concept);
        }

        // Build dependency graph and resolve topological order
        $graph = new DependencyGraph;
        foreach ($applicableConcepts as $concept) {
            $graph->addConcept(
                $concept->code,
                $concept->formula,
                $concept->type,
                $concept,
                $concept->order ?? (is_numeric($concept->code) ? (int) $concept->code : 500)
            );
        }

        $calcOrder = $graph->getCalculationOrder();
        $conceptByCode = $applicableConcepts->keyBy('code');

        $linesData = [];

        foreach ($calcOrder as $code) {
            /** @var PayrollConcept $concept */
            $concept = $conceptByCode->get($code);
            if (! $concept) {
                continue;
            }

            // Prepare concept-specific context variables
            $conceptContext = $context;

            // Quantity / VI for this line
            $lineQuantity = null;
            $lineUnit = null;
            $lineUnitPrice = null;

            if ($code === '004') {
                $conceptContext['VI'] = $cq004;
                $lineQuantity = $cq004;
                $lineUnit = 'Días';
            } elseif ($code === '012') {
                $hours = BigDecimal::of((string) ($input?->overtime_50_hours ?? '0'));
                $conceptContext['VI'] = $hours;
                $lineQuantity = $hours;
                $lineUnit = 'Horas';
            } elseif ($code === '013') {
                $hours = BigDecimal::of((string) ($input?->overtime_100_hours ?? '0'));
                $conceptContext['VI'] = $hours;
                $lineQuantity = $hours;
                $lineUnit = 'Horas';
            } elseif (isset($customValues['VI_'.$code])) {
                $val = BigDecimal::of((string) $customValues['VI_'.$code]);
                $conceptContext['VI'] = $val;
                $lineQuantity = $val;
            } elseif ($concept->code === '001') {
                $lineQuantity = $vi;
                $lineUnit = 'Días';
            } elseif ($concept->code === '111') {
                $lineQuantity = $an;
                $lineUnit = 'Años';
            }

            // IM (Importe / Porcentaje informado o por defecto)
            if (isset($customValues['IM_'.$code])) {
                $conceptContext['IM'] = BigDecimal::of((string) $customValues['IM_'.$code]);
                $lineUnitPrice = $conceptContext['IM'];
            } elseif ($concept->default_amount !== null) {
                $conceptContext['IM'] = BigDecimal::of((string) $concept->default_amount);
                $lineUnitPrice = $conceptContext['IM'];
            }

            // Evaluate formula
            $evaluatedAmount = ExpressionEvaluator::evaluate($concept->formula, $conceptContext)
                ->toScale(2, RoundingMode::HALF_UP);

            // Record in context for future concepts that reference LQxxx
            $context['LQ'.$code] = $evaluatedAmount;

            $remunerativeAmount = BigDecimal::zero();
            $nonTaxableAmount = BigDecimal::zero();
            $deductionAmount = BigDecimal::zero();

            if ($concept->type === 'REMUNERATIVO') {
                $remunerativeAmount = $evaluatedAmount;
                $context['TH'] = $context['TH']->plus($evaluatedAmount);
            } elseif ($concept->type === 'NO_REMUNERATIVO') {
                $nonTaxableAmount = $evaluatedAmount;
                $context['TS'] = $context['TS']->plus($evaluatedAmount);
            } elseif ($concept->type === 'DESCUENTO') {
                $deductionAmount = $evaluatedAmount;
                $context['TD'] = $context['TD']->plus($evaluatedAmount);
            }

            $linesData[] = [
                'payroll_concept_id' => $concept->id,
                'concept_code' => $concept->code,
                'concept_name' => $concept->name,
                'concept_type' => $concept->type,
                'quantity' => $lineQuantity ? (string) $lineQuantity->toScale(2, RoundingMode::HALF_UP) : null,
                'unit' => $lineUnit,
                'unit_price' => $lineUnitPrice ? (string) $lineUnitPrice->toScale(4, RoundingMode::HALF_UP) : null,
                'remunerative_amount' => (string) $remunerativeAmount,
                'non_taxable_amount' => (string) $nonTaxableAmount,
                'deduction_amount' => (string) $deductionAmount,
                'arca_lsd_code' => $concept->arca_lsd_code,
                'order' => $concept->order ?? 500,
                'print_on_receipt' => (bool) $concept->print_on_receipt,
                'calculation_trace' => "Formula: {$concept->formula} | Amount: {$evaluatedAmount}",
            ];
        }

        $grossRemunerative = $context['TH']->toScale(2, RoundingMode::HALF_UP);
        $grossNonRemunerative = $context['TS']->toScale(2, RoundingMode::HALF_UP);
        $totalDeductions = $context['TD']->toScale(2, RoundingMode::HALF_UP);
        $netSalary = $grossRemunerative->plus($grossNonRemunerative)->minus($totalDeductions);

        $netSalaryWords = NumberToWordsConverter::convert((string) $netSalary);

        // Calculate standard employer contributions
        // Jubilacion 10.77%, INSSJP 1.58%, Asig. Fam. 4.70%, FNE 0.95%, Obra Social 6.00%
        $baseContribRem = $grossRemunerative;
        $baseContribSalud = $grossRemunerative->plus($grossNonRemunerative);

        $patronalJub = $baseContribRem->multipliedBy(BigDecimal::of('0.1077'))->toScale(2, RoundingMode::HALF_UP);
        $patronalInssjp = $baseContribRem->multipliedBy(BigDecimal::of('0.0158'))->toScale(2, RoundingMode::HALF_UP);
        $patronalAsig = $baseContribRem->multipliedBy(BigDecimal::of('0.0470'))->toScale(2, RoundingMode::HALF_UP);
        $patronalFne = $baseContribRem->multipliedBy(BigDecimal::of('0.0095'))->toScale(2, RoundingMode::HALF_UP);
        $patronalOS = $baseContribSalud->multipliedBy(BigDecimal::of('0.0600'))->toScale(2, RoundingMode::HALF_UP);
        $patronalTotal = $patronalJub->plus($patronalInssjp)->plus($patronalAsig)->plus($patronalFne)->plus($patronalOS);

        // Create Payslip record
        $payslip = Payslip::create([
            'payroll_run_version_id' => $runVersion->id,
            'employment_id' => $employment->id,
            'file_number' => $employment->file_number,
            'worker_name' => $employment->worker->full_name,
            'worker_cuil' => $employment->worker->cuil,
            'category_code' => $employmentVersion->category_code,
            'category_name' => $category?->name ?? $employmentVersion->category_name ?? $employmentVersion->category_code,
            'hire_date' => $employment->hire_date,
            'seniority_years' => $seniorityYears,
            'health_insurer_code' => $employmentVersion->health_insurer_code,
            'cbu' => $employmentVersion->cbu ?? $employment->worker->cbu,
            'gross_remunerative' => (string) $grossRemunerative,
            'gross_non_remunerative' => (string) $grossNonRemunerative,
            'total_deductions' => (string) $totalDeductions,
            'net_salary' => (string) $netSalary,
            'net_salary_words' => $netSalaryWords,
            'employer_contributions_total' => (string) $patronalTotal,
        ]);

        // Create Payslip Lines
        foreach ($linesData as $line) {
            $line['payslip_id'] = $payslip->id;
            PayslipLine::create($line);
        }

        // Create ARCA LSD Bases
        $bases = [
            'BASE_1' => (string) $grossRemunerative,
            'BASE_2' => (string) $grossRemunerative,
            'BASE_3' => (string) $grossRemunerative,
            'BASE_4' => (string) $baseContribSalud,
            'BASE_5' => (string) $baseContribSalud,
            'BASE_9' => (string) $grossRemunerative,
        ];

        foreach ($bases as $baseCode => $amount) {
            PayslipBase::create([
                'payslip_id' => $payslip->id,
                'base_code' => $baseCode,
                'amount' => $amount,
            ]);
        }

        return $payslip;
    }

    /**
     * Evaluates a condition string against the current settlement context.
     * Supports:
     * - CQ004<0.5
     * - OSO=126205
     * - OSO<>126205
     * - VI=1
     * - VI>0
     */
    public function evaluateCondition(string $condition, array $context): bool
    {
        $cond = trim($condition);
        if ($cond === '' || strtoupper($cond) === 'NONE') {
            return true;
        }

        if (! preg_match('/^([A-Z][A-Z0-9_]*)\s*(<>|!=|==|=|<=|>=|<|>)\s*(-?\d+(?:\.\d+)?)$/i', $cond, $matches)) {
            throw new InvalidArgumentException("Condición de liquidación no reconocida: {$condition}");
        }

        $variable = strtoupper($matches[1]);
        if (! array_key_exists($variable, $context)) {
            throw new InvalidArgumentException("Variable desconocida en condición de liquidación: {$variable}");
        }

        $comparison = BigDecimal::of((string) $context[$variable])
            ->compareTo(BigDecimal::of($matches[3]));

        return match ($matches[2]) {
            '<>', '!=' => $comparison !== 0,
            '=', '==' => $comparison === 0,
            '<' => $comparison < 0,
            '<=' => $comparison <= 0,
            '>' => $comparison > 0,
            '>=' => $comparison >= 0,
        };
    }
}
