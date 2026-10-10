<?php

namespace App\Livewire\Concepts;

use App\Domain\Payroll\Engine\ExpressionEvaluator;
use App\Domain\Payroll\Engine\Parser;
use App\Models\AuditEvent;
use App\Models\PayrollConcept;
use App\Support\Authorization\AuthorizesPermissions;
use App\Support\DecimalFormatter;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class ConceptIndex extends Component
{
    use AuthorizesPermissions, WithPagination;

    public string $search = '';

    public string $typeFilter = 'ALL'; // ALL | REMUNERATIVO | NO_REMUNERATIVO | DESCUENTO

    // Modal state for Concept creation / edit
    public bool $showModal = false;

    public ?int $editingConceptId = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'REMUNERATIVO';

    public string $formula = 'SB';

    public ?string $default_amount = '';

    public ?string $liquidation_condition = '';

    public string $condition_type = 'P'; // P: Porcentual, U: Unidades, T: Todos

    public string $calculation_mode = 'FORMULA'; // FORMULA | INFORMED_VALUE

    public ?string $arca_lsd_code = '';

    public bool $print_on_receipt = true;

    public bool $print_quantity = true;

    public bool $print_unit_price = false;

    public bool $is_automatic = true;

    public int $order = 10;

    public string $status = 'ACTIVE';

    // Interactive Formula Tester in Modal
    public bool $showTester = false;

    public string $test_sb = '600000.00';

    public string $test_an = '2';

    public string $test_vi = '1';

    public string $test_im = '11';

    public string $test_th = '650000.00';

    public string $test_ts = '60000.00';

    public string $test_result = '';

    public string $test_error = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'ALL'],
    ];

    public function mount(): void
    {
        $this->authorizeAnyPermission('agreements.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $this->resetErrorBag();
        $this->editingConceptId = null;
        $this->code = '';
        $this->name = '';
        $this->type = 'REMUNERATIVO';
        $this->formula = 'SB';
        $this->default_amount = '';
        $this->liquidation_condition = '';
        $this->condition_type = 'P';
        $this->calculation_mode = 'FORMULA';
        $this->arca_lsd_code = '110000';
        $this->print_on_receipt = true;
        $this->print_quantity = true;
        $this->print_unit_price = false;
        $this->is_automatic = true;
        $this->order = ((PayrollConcept::global()->max('order') ?? 0) + 10);
        $this->status = 'ACTIVE';
        $this->showTester = false;
        $this->test_result = '';
        $this->test_error = '';
        $this->showModal = true;
    }

    public function editConcept(int $id): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $this->resetErrorBag();
        $concept = PayrollConcept::findOrFail($id);

        $this->editingConceptId = $concept->id;
        $this->code = $concept->code;
        $this->name = $concept->name;
        $this->type = $concept->type;
        $this->formula = $concept->formula;
        $this->default_amount = $concept->default_amount !== null ? (string) $concept->default_amount : '';
        $this->liquidation_condition = $concept->liquidation_condition ?? '';
        $this->condition_type = $concept->condition_type ?? 'P';
        $this->calculation_mode = $concept->calculation_mode;
        $this->arca_lsd_code = $concept->arca_lsd_code ?? '';
        $this->print_on_receipt = (bool) $concept->print_on_receipt;
        $this->print_quantity = (bool) $concept->print_quantity;
        $this->print_unit_price = (bool) $concept->print_unit_price;
        $this->is_automatic = (bool) $concept->is_automatic;
        $this->order = (int) $concept->order;
        $this->status = $concept->status;
        $this->showTester = false;
        $this->test_result = '';
        $this->test_error = '';
        $this->showModal = true;
    }

    public function testCurrentFormula(): void
    {
        $this->test_error = '';
        $this->test_result = '';

        if (empty(trim($this->formula))) {
            $this->test_error = 'La fórmula no puede estar vacía.';

            return;
        }

        try {
            // Validate AST syntax first
            Parser::parseString($this->formula);

            $context = [
                'SB' => $this->test_sb,
                'AN' => $this->test_an,
                'VI' => $this->test_vi,
                'IM' => ! empty($this->default_amount) ? $this->default_amount : $this->test_im,
                'TH' => $this->test_th,
                'TS' => $this->test_ts,
                'TD' => '0.00',
                'NR' => $this->test_ts,
                'SID' => '1',
                'SIM' => '1',
            ];

            $result = ExpressionEvaluator::evaluateRounded($this->formula, $context);
            $this->test_result = '$ '.DecimalFormatter::format($result);
        } catch (Exception $e) {
            $this->test_error = 'Error de sintaxis: '.$e->getMessage();
        }
    }

    public function saveConcept(): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $this->validate([
            'code' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:REMUNERATIVO,NO_REMUNERATIVO,DESCUENTO'],
            'formula' => ['required', 'string', 'max:255'],
            'calculation_mode' => ['required', 'in:FORMULA,INFORMED_VALUE'],
            'order' => ['required', 'integer'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ], [
            'code.required' => 'El código del concepto es obligatorio.',
            'name.required' => 'La descripción del concepto es obligatoria.',
            'formula.required' => 'La fórmula es obligatoria.',
        ]);

        // Validate AST syntax
        if ($this->calculation_mode === 'FORMULA') {
            try {
                Parser::parseString($this->formula);
            } catch (Exception $e) {
                $this->addError('formula', 'Fórmula no válida: '.$e->getMessage());

                return;
            }
        }

        $data = [
            'agreement_id' => null, // Concepts are global across all agreements
            'employer_id' => null,
            'code' => trim($this->code),
            'name' => trim($this->name),
            'type' => $this->type,
            'formula' => trim($this->formula),
            'default_amount' => $this->default_amount !== '' && is_numeric($this->default_amount) ? $this->default_amount : null,
            'liquidation_condition' => ! empty(trim($this->liquidation_condition)) ? trim($this->liquidation_condition) : null,
            'condition_type' => $this->condition_type,
            'calculation_mode' => $this->calculation_mode,
            'arca_lsd_code' => ! empty(trim($this->arca_lsd_code)) ? trim($this->arca_lsd_code) : null,
            'print_on_receipt' => $this->print_on_receipt,
            'print_quantity' => $this->print_quantity,
            'print_unit_price' => $this->print_unit_price,
            'is_automatic' => $this->is_automatic,
            'order' => $this->order,
            'status' => $this->status,
        ];

        if ($this->editingConceptId) {
            $concept = PayrollConcept::findOrFail($this->editingConceptId);
            $concept->update($data);
            $action = 'ACTUALIZAR_CONCEPTO_GLOBAL';
        } else {
            $concept = PayrollConcept::create($data);
            $action = 'CREAR_CONCEPTO_GLOBAL';
        }

        AuditEvent::record(
            action: $action,
            entityType: 'PayrollConcept',
            entityId: $concept->id,
            newValues: $data,
            reason: "Concepto global {$concept->code} ({$concept->name}) ".($this->editingConceptId ? 'modificado' : 'creado').' para todos los convenios.'
        );

        session()->flash('success', "Concepto global {$concept->code} - {$concept->name} guardado correctamente. Aplica a todos los convenios.");
        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $concept = PayrollConcept::findOrFail($id);
        $newStatus = $concept->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $concept->update(['status' => $newStatus]);

        AuditEvent::record(
            action: 'CAMBIAR_ESTADO_CONCEPTO',
            entityType: 'PayrollConcept',
            entityId: $concept->id,
            newValues: ['status' => $newStatus],
            reason: "Estado del concepto {$concept->code} cambiado a {$newStatus}"
        );

        session()->flash('success', "Concepto {$concept->code} ahora está ".($newStatus === 'ACTIVE' ? 'Activo' : 'Inactivo').'.');
    }

    public function render()
    {
        $this->authorizeAnyPermission('agreements.view');
        $query = PayrollConcept::global();

        if (! empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                    ->orWhere('arca_lsd_code', 'like', "%{$s}%")
                    ->orWhere('formula', 'like', "%{$s}%");
            });
        }

        if ($this->typeFilter !== 'ALL') {
            $query->where('type', $this->typeFilter);
        }

        $concepts = $query->orderBy('order')->orderBy('code')->paginate(20);

        // Metrics for summary cards
        $totalCount = PayrollConcept::global()->count();
        $remCount = PayrollConcept::global()->where('type', 'REMUNERATIVO')->count();
        $nonTaxCount = PayrollConcept::global()->where('type', 'NO_REMUNERATIVO')->count();
        $discCount = PayrollConcept::global()->where('type', 'DESCUENTO')->count();

        return view('livewire.concepts.concept-index', [
            'concepts' => $concepts,
            'totalCount' => $totalCount,
            'remCount' => $remCount,
            'nonTaxCount' => $nonTaxCount,
            'discCount' => $discCount,
        ])->layout('components.layouts.app');
    }
}
