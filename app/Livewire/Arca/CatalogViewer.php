<?php

namespace App\Livewire\Arca;

use App\Models\ArcaCatalog;
use App\Support\Authorization\AuthorizesPermissions;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogViewer extends Component
{
    use AuthorizesPermissions, WithPagination;

    public string $typeFilter = 'OBRA_SOCIAL';

    public string $search = '';

    public function mount(): void
    {
        $this->authorizeAnyPermission('catalogs.view');
    }

    public function setType(string $type): void
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    public function render()
    {
        $this->authorizeAnyPermission('catalogs.view');
        $query = ArcaCatalog::ofType($this->typeFilter);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        $items = $query->orderBy('code')->paginate(15);

        return view('livewire.arca.catalog-viewer', [
            'items' => $items,
        ])->layout('components.layouts.app', ['title' => 'Catálogos Oficiales ARCA — Sueldos Web']);
    }
}
