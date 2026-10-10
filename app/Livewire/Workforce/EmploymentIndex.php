<?php

namespace App\Livewire\Workforce;

use App\Domain\Tenant\EmployerContext;
use App\Models\Agreement;
use App\Models\AgreementCategory;
use App\Models\ArcaCatalog;
use App\Models\AuditEvent;
use App\Models\Employment;
use App\Models\EmploymentVersion;
use App\Models\Worker;
use Livewire\Component;
use Livewire\WithPagination;

class EmploymentIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'ACTIVE';

    // Modal state
    public bool $showModal = false;
    public ?int $editingEmploymentId = null;

    // Worker fields
    public string $cuil = '';
    public string $first_name = '';
    public string $last_name = '';

    // Employment fields
    public string $file_number = '';
    public string $hire_date = '';
    public string $seniority_date = '';
    public string $employment_status = 'ACTIVE';

    // Version fields
    public string $agreement_code = '003'; // Comercio
    public string $category_code = '019';
    public string $category_name = 'VENDEDOR B';
    public int $workday_type = 1; // 1: Completa, 2: 3/4, 3: 1/2, 4: 1/4
    public string $health_insurer_code = '126205'; // OSECAC
    public string $cbu = '';
    public string $agreed_salary = '';

    // Scale info properties
    public float $scale_basic = 0.0;
    public float $scale_non_taxable = 0.0;
    public float $suggested_basic = 0.0;

    protected function rules(): array
    {
        return [
            'cuil' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!Worker::isValidCuil($value)) {
                    $fail('El CUIL no es válido según el algoritmo oficial Módulo 11.');
                }
            }],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'file_number' => ['required', 'string', 'max:10'],
            'hire_date' => ['required', 'date'],
            'seniority_date' => ['required', 'date'],
            'agreement_code' => ['required', 'string'],
            'category_code' => ['required', 'string'],
            'category_name' => ['nullable', 'string', 'max:100'],
            'workday_type' => ['required', 'in:1,2,3,4'],
            'health_insurer_code' => ['required', 'string'],
            'cbu' => ['nullable', 'string', 'size:22'],
            'agreed_salary' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function updatedAgreementCode(): void
    {
        $agreement = Agreement::where('code', $this->agreement_code)->first();
        if ($agreement) {
            $firstCat = $agreement->categories()->first();
            if ($firstCat) {
                $this->category_code = $firstCat->code;
                $this->category_name = $firstCat->name;
            }
        }
        $this->updateScaleInfo();
    }

    public function updatedCategoryCode(): void
    {
        $agreement = Agreement::where('code', $this->agreement_code)->first();
        if ($agreement) {
            $cat = $agreement->categories()->where('code', $this->category_code)->first();
            if ($cat) {
                $this->category_name = $cat->name;
            }
        }
        $this->updateScaleInfo();
    }

    public function updatedWorkdayType(): void
    {
        $this->calculateSuggestedSalary();
    }

    public function updateScaleInfo(): void
    {
        $agreement = Agreement::where('code', $this->agreement_code)->first();
        if ($agreement) {
            $cat = $agreement->categories()->with(['currentScale', 'latestScale'])->where('code', $this->category_code)->first();
            if ($cat) {
                $scale = $cat->currentScale ?? $cat->latestScale;
                $this->scale_basic = (float) ($scale?->basic_salary ?? 0);
                $this->scale_non_taxable = (float) ($scale?->non_taxable_salary ?? 0);
                $this->calculateSuggestedSalary();
                return;
            }
        }

        $this->scale_basic = 0.0;
        $this->scale_non_taxable = 0.0;
        $this->suggested_basic = 0.0;
    }

    protected function calculateSuggestedSalary(): void
    {
        $factor = match ((int) $this->workday_type) {
            1 => 1.0,    // Jornada completa
            2 => 0.75,   // 3/4 jornada
            3 => 0.50,   // Media jornada
            4 => 0.25,   // 1/4 jornada
            default => 1.0,
        };

        $this->suggested_basic = round($this->scale_basic * $factor, 2);

        // Pre-fill or suggest agreed_salary if empty or when creating
        if (empty($this->agreed_salary) || !$this->editingEmploymentId) {
            $this->agreed_salary = (string) $this->suggested_basic;
        }
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'editingEmploymentId', 'cuil', 'first_name', 'last_name',
            'file_number', 'cbu', 'agreed_salary'
        ]);
        $this->hire_date = now()->toDateString();
        $this->seniority_date = now()->toDateString();
        $this->employment_status = 'ACTIVE';
        $this->agreement_code = '003';
        $this->category_code = '019';
        $this->category_name = 'VENDEDOR B';
        $this->workday_type = 1;
        $this->health_insurer_code = '126205';

        $this->updateScaleInfo();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(int $employmentId): void
    {
        $employment = Employment::with(['worker', 'currentVersion'])->findOrFail($employmentId);
        $this->editingEmploymentId = $employment->id;

        $worker = $employment->worker;
        $this->cuil = $worker->formatted_cuil;
        $this->first_name = $worker->first_name;
        $this->last_name = $worker->last_name;

        $this->file_number = $employment->file_number;
        $this->hire_date = $employment->hire_date->format('Y-m-d');
        $this->seniority_date = $employment->seniority_date->format('Y-m-d');
        $this->employment_status = $employment->status;

        $version = $employment->currentVersion ?? $employment->latestVersion;
        if ($version) {
            $this->agreement_code = $version->agreement_code;
            $this->category_code = $version->category_code;
            $this->category_name = $version->category_name ?? '';
            $this->workday_type = $version->workday_type;
            $this->health_insurer_code = $version->health_insurer_code;
            $this->cbu = $version->cbu ?? '';
            $this->agreed_salary = (string) ($version->agreed_salary ?? '');
        }

        $this->updateScaleInfo();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveEmployment(): void
    {
        $this->validate();

        $activeEmployerId = EmployerContext::getActiveEmployerId();
        if (!$activeEmployerId) {
            session()->flash('error', 'Debe seleccionar una empresa activa primero.');
            return;
        }

        $cleanCuil = preg_replace('/\D/', '', $this->cuil);

        // Find or create worker
        $worker = Worker::firstOrCreate(
            ['cuil' => $cleanCuil],
            ['first_name' => $this->first_name, 'last_name' => $this->last_name]
        );
        $worker->update(['first_name' => $this->first_name, 'last_name' => $this->last_name]);

        if ($this->editingEmploymentId) {
            $employment = Employment::findOrFail($this->editingEmploymentId);
            $oldValues = $employment->toArray();

            $employment->update([
                'file_number' => $this->file_number,
                'hire_date' => $this->hire_date,
                'seniority_date' => $this->seniority_date,
                'status' => $this->employment_status,
                'lock_version' => $employment->lock_version + 1,
            ]);

            EmploymentVersion::create([
                'employment_id' => $employment->id,
                'employer_id' => $activeEmployerId,
                'effective_from' => now()->toDateString(),
                'agreement_code' => $this->agreement_code,
                'category_code' => $this->category_code,
                'category_name' => $this->category_name,
                'workday_type' => $this->workday_type,
                'health_insurer_code' => $this->health_insurer_code,
                'cbu' => !empty($this->cbu) ? $this->cbu : null,
                'agreed_salary' => !empty($this->agreed_salary) ? $this->agreed_salary : null,
            ]);

            AuditEvent::record('UPDATE', 'Employment', $employment->id, $oldValues, $employment->toArray(), 'Actualización de legajo de personal');
            session()->flash('success', "Legajo {$employment->file_number} ({$worker->full_name}) actualizado.");
        } else {
            // Verify file number unique for employer
            if (Employment::where('employer_id', $activeEmployerId)->where('file_number', $this->file_number)->exists()) {
                $this->addError('file_number', 'Ya existe un legajo con este número en esta empresa.');
                return;
            }

            $employment = Employment::create([
                'employer_id' => $activeEmployerId,
                'worker_id' => $worker->id,
                'file_number' => $this->file_number,
                'hire_date' => $this->hire_date,
                'seniority_date' => $this->seniority_date,
                'status' => $this->employment_status,
            ]);

            EmploymentVersion::create([
                'employment_id' => $employment->id,
                'employer_id' => $activeEmployerId,
                'effective_from' => $this->hire_date,
                'agreement_code' => $this->agreement_code,
                'category_code' => $this->category_code,
                'category_name' => $this->category_name,
                'workday_type' => $this->workday_type,
                'health_insurer_code' => $this->health_insurer_code,
                'cbu' => !empty($this->cbu) ? $this->cbu : null,
                'agreed_salary' => !empty($this->agreed_salary) ? $this->agreed_salary : null,
            ]);

            AuditEvent::record('CREATE', 'Employment', $employment->id, null, $employment->toArray(), 'Alta de nuevo legajo de personal');
            session()->flash('success', "Legajo {$employment->file_number} ({$worker->full_name}) registrado.");
        }

        $this->showModal = false;
    }

    public function render()
    {
        $activeEmployer = EmployerContext::getActiveEmployer();

        $query = Employment::with(['worker', 'currentVersion'])
            ->join('workers', 'employments.worker_id', '=', 'workers.id')
            ->select('employments.*');

        if (!empty($this->search)) {
            $cleanSearch = preg_replace('/\D/', '', $this->search);
            $query->where(function ($q) use ($cleanSearch) {
                $q->where('employments.file_number', 'like', "%{$this->search}%")
                  ->orWhere('workers.first_name', 'like', "%{$this->search}%")
                  ->orWhere('workers.last_name', 'like', "%{$this->search}%");
                if (!empty($cleanSearch)) {
                    $q->orWhere('workers.cuil', 'like', "%{$cleanSearch}%");
                }
            });
        }

        if ($this->statusFilter !== 'ALL') {
            $query->where('employments.status', $this->statusFilter);
        }

        $employments = $query->orderBy('employments.file_number')->paginate(10);
        $obrasSociales = ArcaCatalog::getObrasSociales();
        $agreements = Agreement::active()->get();

        $activeAgreement = Agreement::where('code', $this->agreement_code)->first();
        $availableCategories = $activeAgreement ? $activeAgreement->categories()->active()->get() : collect();

        return view('livewire.workforce.employment-index', [
            'employments' => $employments,
            'activeEmployer' => $activeEmployer,
            'obrasSociales' => $obrasSociales,
            'agreements' => $agreements,
            'availableCategories' => $availableCategories,
        ])->layout('components.layouts.app', ['title' => 'Legajos de Personal — Sueldos Web']);
    }
}
