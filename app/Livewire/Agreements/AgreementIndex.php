<?php

namespace App\Livewire\Agreements;

use App\Domain\Payroll\Engine\Parser;
use App\Models\Agreement;
use App\Models\AgreementCategory;
use App\Models\AuditEvent;
use App\Models\CategoryScaleVersion;
use App\Models\PayrollConcept;
use App\Support\Authorization\AuthorizesPermissions;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AgreementIndex extends Component
{
    use AuthorizesPermissions;

    public ?int $selectedAgreementId = null;
    public string $search = '';
    public string $activeTab = 'CATEGORIES'; // 'CATEGORIES' | 'CONCEPTS'

    // Modal state for Category
    public bool $showCategoryModal = false;
    public ?int $editingCategoryId = null;
    public string $category_code = '';
    public string $category_name = '';
    public string $condition = 'M';
    public string $basic_salary = '';
    public string $non_taxable_salary = '';
    public string $effective_from = '';
    public string $notes = '';

    // Modal state for Concept
    public bool $showConceptModal = false;
    public ?int $editingConceptId = null;
    public string $concept_code = '';
    public string $concept_name = '';
    public string $concept_type = 'REMUNERATIVO';
    public string $concept_formula = 'SB';
    public string $concept_calculation_mode = 'FORMULA';
    public string $concept_arca_lsd_code = '';
    public bool $concept_print_on_receipt = true;

    // Modal state for Agreement
    public bool $showAgreementModal = false;
    public string $new_agreement_code = '';
    public string $new_agreement_name = '';
    public string $new_agreement_description = '';

    public function mount(): void
    {
        $this->authorizeAnyPermission('agreements.view');
        $first = Agreement::active()->first();
        if ($first) {
            $this->selectedAgreementId = $first->id;
        }
        $this->effective_from = now()->toDateString();
    }

    public function selectAgreement(int $id): void
    {
        $this->selectedAgreementId = $id;
        $this->search = '';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->search = '';
    }

    // --- CATEGORY CRUD ---

    public function openCreateCategoryModal(): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $this->reset([
            'editingCategoryId', 'category_code', 'category_name',
            'basic_salary', 'non_taxable_salary', 'notes'
        ]);
        $this->condition = 'M';
        $this->effective_from = now()->toDateString();
        $this->resetValidation();
        $this->showCategoryModal = true;
    }

    public function openEditCategoryModal(int $categoryId): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $cat = AgreementCategory::with('currentScale', 'latestScale')->findOrFail($categoryId);
        $this->editingCategoryId = $cat->id;
        $this->category_code = $cat->code;
        $this->category_name = $cat->name;
        $this->condition = $cat->condition;

        $scale = $cat->currentScale ?? $cat->latestScale;
        $this->basic_salary = (string) ($scale?->basic_salary ?? '0');
        $this->non_taxable_salary = (string) ($scale?->non_taxable_salary ?? '0');
        $this->effective_from = ($scale?->effective_from?->format('Y-m-d')) ?? now()->toDateString();
        $this->notes = $scale?->notes ?? '';

        $this->resetValidation();
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $this->validate([
            'category_code' => ['required', 'string', 'max:10'],
            'category_name' => ['required', 'string', 'max:100'],
            'condition' => ['required', 'in:M,D,H'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'non_taxable_salary' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
        ]);

        if ($this->editingCategoryId) {
            $cat = AgreementCategory::findOrFail($this->editingCategoryId);
            $cat->update([
                'code' => $this->category_code,
                'name' => $this->category_name,
                'condition' => $this->condition,
            ]);

            CategoryScaleVersion::create([
                'agreement_category_id' => $cat->id,
                'effective_from' => $this->effective_from,
                'basic_salary' => $this->basic_salary,
                'non_taxable_salary' => !empty($this->non_taxable_salary) ? $this->non_taxable_salary : 0,
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ]);

            AuditEvent::record('UPDATE', 'AgreementCategory', $cat->id, null, [
                'name' => $cat->name,
                'basic' => $this->basic_salary,
                'non_taxable' => $this->non_taxable_salary,
            ], "Actualización de escala salarial categoría {$cat->name}");

            session()->flash('success', "Categoría {$cat->name} y escala salarial actualizadas.");
        } else {
            if (AgreementCategory::where('agreement_id', $this->selectedAgreementId)->where('code', $this->category_code)->exists()) {
                $this->addError('category_code', 'Ya existe una categoría con este código en el convenio seleccionado.');
                return;
            }

            $cat = AgreementCategory::create([
                'agreement_id' => $this->selectedAgreementId,
                'code' => $this->category_code,
                'name' => $this->category_name,
                'condition' => $this->condition,
                'status' => 'ACTIVE',
            ]);

            CategoryScaleVersion::create([
                'agreement_category_id' => $cat->id,
                'effective_from' => $this->effective_from,
                'basic_salary' => $this->basic_salary,
                'non_taxable_salary' => !empty($this->non_taxable_salary) ? $this->non_taxable_salary : 0,
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ]);

            AuditEvent::record('CREATE', 'AgreementCategory', $cat->id, null, [
                'name' => $cat->name,
                'basic' => $this->basic_salary,
                'non_taxable' => $this->non_taxable_salary,
            ], "Alta de nueva categoría {$cat->name} con básico y no remunerativo");

            session()->flash('success', "Categoría {$cat->name} creada con éxito.");
        }

        $this->showCategoryModal = false;
    }

    // --- CONCEPT CRUD ---

    public function openCreateConceptModal(): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $this->reset([
            'editingConceptId', 'concept_code', 'concept_name',
            'concept_arca_lsd_code'
        ]);
        $this->concept_type = 'REMUNERATIVO';
        $this->concept_formula = 'SB';
        $this->concept_calculation_mode = 'FORMULA';
        $this->concept_print_on_receipt = true;

        $this->resetValidation();
        $this->showConceptModal = true;
    }

    public function openEditConceptModal(int $conceptId): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $concept = PayrollConcept::findOrFail($conceptId);
        $this->editingConceptId = $concept->id;
        $this->concept_code = $concept->code;
        $this->concept_name = $concept->name;
        $this->concept_type = $concept->type;
        $this->concept_formula = $concept->formula;
        $this->concept_calculation_mode = $concept->calculation_mode;
        $this->concept_arca_lsd_code = $concept->arca_lsd_code ?? '';
        $this->concept_print_on_receipt = (bool) $concept->print_on_receipt;

        $this->resetValidation();
        $this->showConceptModal = true;
    }

    public function saveConcept(): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $this->validate([
            'concept_code' => ['required', 'string', 'max:10'],
            'concept_name' => ['required', 'string', 'max:150'],
            'concept_type' => ['required', 'in:REMUNERATIVO,NO_REMUNERATIVO,DESCUENTO'],
            'concept_formula' => ['required', 'string', 'max:255'],
            'concept_calculation_mode' => ['required', 'in:FORMULA,INFORMED_VALUE'],
            'concept_arca_lsd_code' => ['nullable', 'string', 'max:10'],
        ]);

        // Syntactic formula validation using the safe AST parser!
        try {
            Parser::parseString($this->concept_formula);
        } catch (Exception $e) {
            $this->addError('concept_formula', 'Fórmula no válida: ' . $e->getMessage());
            return;
        }

        if ($this->editingConceptId) {
            $concept = PayrollConcept::findOrFail($this->editingConceptId);
            $concept->update([
                'code' => $this->concept_code,
                'name' => $this->concept_name,
                'type' => $this->concept_type,
                'formula' => $this->concept_formula,
                'calculation_mode' => $this->concept_calculation_mode,
                'arca_lsd_code' => $this->concept_arca_lsd_code ?: null,
                'print_on_receipt' => $this->concept_print_on_receipt,
            ]);

            AuditEvent::record('UPDATE', 'PayrollConcept', $concept->id, null, $concept->toArray(), "Edición de concepto {$concept->code} ({$concept->name})");
            session()->flash('success', "Concepto {$concept->code} actualizado correctamente.");
        } else {
            if (PayrollConcept::global()->where('code', $this->concept_code)->exists()) {
                $this->addError('concept_code', 'Ya existe un concepto global con este código en el estudio.');
                return;
            }

            $concept = PayrollConcept::create([
                'agreement_id' => null, // Concepts are global across all agreements
                'code' => $this->concept_code,
                'name' => $this->concept_name,
                'type' => $this->concept_type,
                'formula' => $this->concept_formula,
                'calculation_mode' => $this->concept_calculation_mode,
                'arca_lsd_code' => $this->concept_arca_lsd_code ?: null,
                'print_on_receipt' => $this->concept_print_on_receipt,
                'status' => 'ACTIVE',
            ]);

            AuditEvent::record('CREATE', 'PayrollConcept', $concept->id, null, $concept->toArray(), "Alta de concepto global {$concept->code} ({$concept->name})");
            session()->flash('success', "Concepto global {$concept->code} creado con éxito.");
        }

        $this->showConceptModal = false;
    }

    // --- AGREEMENT CRUD ---

    public function openCreateAgreementModal(): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $this->reset(['new_agreement_code', 'new_agreement_name', 'new_agreement_description']);
        $this->resetValidation();
        $this->showAgreementModal = true;
    }

    public function saveAgreement(): void
    {
        $this->authorizeGlobalPermission('agreements.manage');
        $this->validate([
            'new_agreement_code' => ['required', 'string', 'max:10', 'unique:agreements,code'],
            'new_agreement_name' => ['required', 'string', 'max:150'],
            'new_agreement_description' => ['nullable', 'string', 'max:255'],
        ]);

        $agreement = Agreement::create([
            'code' => $this->new_agreement_code,
            'name' => $this->new_agreement_name,
            'description' => $this->new_agreement_description,
            'status' => 'ACTIVE',
        ]);

        $this->selectedAgreementId = $agreement->id;
        AuditEvent::record('CREATE', 'Agreement', $agreement->id, null, $agreement->toArray(), "Alta de convenio {$agreement->name}");
        session()->flash('success', "Convenio {$agreement->name} creado.");
        $this->showAgreementModal = false;
    }

    public function render()
    {
        $this->authorizeAnyPermission('agreements.view');
        $agreements = Agreement::active()->withCount('categories')->get();
        $selectedAgreement = $this->selectedAgreementId ? Agreement::find($this->selectedAgreementId) : null;

        $categoriesQuery = AgreementCategory::with(['currentScale', 'latestScale'])
            ->where('agreement_id', $this->selectedAgreementId);

        $conceptsQuery = PayrollConcept::global();

        if (!empty($this->search)) {
            $categoriesQuery->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%");
            });

            $conceptsQuery->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%")
                  ->orWhere('formula', 'like', "%{$this->search}%");
            });
        }

        $categories = $categoriesQuery->orderBy('code')->get();
        $concepts = $conceptsQuery->orderBy('order')->orderBy('code')->get();

        return view('livewire.agreements.agreement-index', [
            'agreements' => $agreements,
            'selectedAgreement' => $selectedAgreement,
            'categories' => $categories,
            'concepts' => $concepts,
        ])->layout('components.layouts.app', ['title' => 'Convenios y Fórmulas — Sueldos Web']);
    }
}
