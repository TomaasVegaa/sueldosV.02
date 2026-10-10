<?php

namespace App\Livewire\Employers;

use App\Domain\Tenant\EmployerContext;
use App\Models\AuditEvent;
use App\Models\Employer;
use App\Models\EmployerSettingsVersion;
use App\Support\Authorization\AuthorizesPermissions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EmployerIndex extends Component
{
    use AuthorizesPermissions, WithPagination;

    public string $search = '';
    public string $statusFilter = 'ALL';

    // Form Modal state
    public bool $showModal = false;
    public ?int $editingEmployerId = null;
    public string $cuit = '';
    public string $legal_name = '';
    public string $status = 'ACTIVE';
    public string $payment_place = 'San Miguel de Tucumán';
    public string $contribution_bank = 'Banco Macro S.A.';
    public string $notes = '';

    public function mount(): void
    {
        $this->authorizeAnyPermission('employers.view');
    }

    protected function rules(): array
    {
        return [
            'cuit' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!Employer::isValidCuit($value)) {
                    $fail('El CUIT ingresado no es válido según el algoritmo oficial Módulo 11.');
                }
            }],
            'legal_name' => ['required', 'string', 'max:200'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'payment_place' => ['nullable', 'string', 'max:150'],
            'contribution_bank' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function openCreateModal(): void
    {
        $this->authorizeGlobalPermission('employers.manage');
        $this->reset(['cuit', 'legal_name', 'notes', 'editingEmployerId']);
        $this->status = 'ACTIVE';
        $this->payment_place = 'San Miguel de Tucumán';
        $this->contribution_bank = 'Banco Macro S.A.';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->authorizeEmployerPermission('employers.manage', $id);
        $employer = Employer::with('currentSettings')->findOrFail($id);
        $this->editingEmployerId = $employer->id;
        $this->cuit = $employer->formatted_cuit;
        $this->legal_name = $employer->legal_name;
        $this->status = $employer->status;

        $settings = $employer->currentSettings;
        $this->payment_place = $settings?->payment_place ?? '';
        $this->contribution_bank = $settings?->contribution_bank ?? '';
        $this->notes = $settings?->notes ?? '';

        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveEmployer(): void
    {
        $this->validate();

        $cleanCuit = preg_replace('/\D/', '', $this->cuit);

        if ($this->editingEmployerId) {
            $this->authorizeEmployerPermission('employers.manage', $this->editingEmployerId);
            $employer = Employer::findOrFail($this->editingEmployerId);
            $oldValues = $employer->toArray();

            $employer->update([
                'cuit' => $cleanCuit,
                'legal_name' => $this->legal_name,
                'status' => $this->status,
                'lock_version' => $employer->lock_version + 1,
            ]);

            // Create or update settings version
            EmployerSettingsVersion::create([
                'employer_id' => $employer->id,
                'effective_from' => now()->toDateString(),
                'payment_place' => $this->payment_place,
                'contribution_bank' => $this->contribution_bank,
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ]);

            AuditEvent::record('UPDATE', 'Employer', $employer->id, $oldValues, $employer->toArray(), 'Edición de empresa y parámetros');
            session()->flash('success', "Empresa {$employer->legal_name} actualizada con éxito.");
        } else {
            $this->authorizeGlobalPermission('employers.manage');
            // Check unique CUIT
            if (Employer::where('cuit', $cleanCuit)->exists()) {
                $this->addError('cuit', 'Ya existe una empresa registrada con este CUIT.');
                return;
            }

            $employer = Employer::create([
                'cuit' => $cleanCuit,
                'legal_name' => $this->legal_name,
                'status' => $this->status,
            ]);

            EmployerSettingsVersion::create([
                'employer_id' => $employer->id,
                'effective_from' => now()->toDateString(),
                'payment_place' => $this->payment_place,
                'contribution_bank' => $this->contribution_bank,
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ]);

            AuditEvent::record('CREATE', 'Employer', $employer->id, null, $employer->toArray(), 'Alta de nueva empresa cliente');
            session()->flash('success', "Empresa {$employer->legal_name} creada exitosamente.");
        }

        $this->showModal = false;
    }

    public function activateEmployer(int $id): void
    {
        $this->authorizeEmployerPermission('employers.view', $id);
        $employer = Employer::findOrFail($id);
        EmployerContext::setActiveEmployerId($employer->id);

        AuditEvent::record('SWITCH_TENANT', 'Employer', $employer->id, null, ['id' => $employer->id], "Contexto activo fijado en {$employer->legal_name}");
        session()->flash('success', "Contexto de trabajo cambiado a: {$employer->legal_name}");
        $this->redirect(route('employers.index'));
    }

    public function render()
    {
        $this->authorizeAnyPermission('employers.view');

        $accessibleIds = Auth::user()->accessibleEmployers()->pluck('id');
        $query = Employer::with('currentSettings')
            ->whereIn('id', $accessibleIds)
            ->withCount(['employments' => fn ($q) => $q
                ->withoutGlobalScopes()
                ->where('status', 'ACTIVE')]);

        if (!empty($this->search)) {
            $cleanSearch = preg_replace('/\D/', '', $this->search);
            $query->where(function ($q) use ($cleanSearch) {
                $q->where('legal_name', 'like', "%{$this->search}%");
                if (!empty($cleanSearch)) {
                    $q->orWhere('cuit', 'like', "%{$cleanSearch}%");
                }
            });
        }

        if ($this->statusFilter !== 'ALL') {
            $query->where('status', $this->statusFilter);
        }

        $employers = $query->orderBy('legal_name')->paginate(10);
        $activeEmployerId = EmployerContext::getActiveEmployerId();

        return view('livewire.employers.employer-index', [
            'employers' => $employers,
            'activeEmployerId' => $activeEmployerId,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Empresas — Sueldos Web']);
    }
}
