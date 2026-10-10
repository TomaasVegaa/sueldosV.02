<?php

namespace App\Livewire;

use App\Domain\Tenant\EmployerContext;
use App\Models\AuditEvent;
use App\Models\Employer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HeaderEmployerSelector extends Component
{
    public ?int $selectedEmployerId = null;

    public function mount(): void
    {
        $this->selectedEmployerId = EmployerContext::getActiveEmployerId();
    }

    public function selectEmployer(int $employerId): void
    {
        $user = Auth::user();
        $accessible = $user ? $user->accessibleEmployers()->pluck('id')->toArray() : [];

        if (in_array($employerId, $accessible) || ($user && $user->isSuperAdmin())) {
            $prev = EmployerContext::getActiveEmployerId();
            EmployerContext::setActiveEmployerId($employerId);
            $this->selectedEmployerId = $employerId;

            $emp = Employer::find($employerId);
            AuditEvent::record('SWITCH_TENANT', 'Employer', $employerId, ['previous' => $prev], ['new' => $employerId], "Operador cambió contexto a {$emp?->legal_name}");

            $this->redirect(request()->header('Referer', route('employers.index')));
        }
    }

    public function render()
    {
        $user = Auth::user();
        $employers = $user ? $user->accessibleEmployers() : collect();
        $activeEmployer = EmployerContext::getActiveEmployer();

        return view('livewire.header-employer-selector', [
            'employers' => $employers,
            'activeEmployer' => $activeEmployer,
        ]);
    }
}
