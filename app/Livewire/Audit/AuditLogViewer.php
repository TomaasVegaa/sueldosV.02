<?php

namespace App\Livewire\Audit;

use App\Models\AuditEvent;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $actionFilter = 'ALL';
    public string $search = '';

    public function render()
    {
        $query = AuditEvent::with(['user', 'employer'])->orderByDesc('created_at');

        if ($this->actionFilter !== 'ALL') {
            $query->where('action', $this->actionFilter);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reason', 'like', "%{$this->search}%")
                  ->orWhere('entity_type', 'like', "%{$this->search}%")
                  ->orWhere('ip_address', 'like', "%{$this->search}%");
            });
        }

        $events = $query->paginate(20);

        return view('livewire.audit.audit-log-viewer', [
            'events' => $events,
        ])->layout('components.layouts.app', ['title' => 'Trazabilidad y Auditoría — Sueldos Web']);
    }
}
