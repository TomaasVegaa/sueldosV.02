<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Empresas Clientes</h1>
            <p class="text-xs text-slate-500 mt-0.5">Gestión de empleadores, parámetros fiscales y aislamiento multi-empresa.</p>
        </div>
        <button wire:click="openCreateModal" 
                type="button" 
                class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Empresa
        </button>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-96">
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Buscar por Razón Social o CUIT..." 
                   class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <div class="flex items-center space-x-2 w-full md:w-auto">
            <span class="text-xs text-slate-500">Estado:</span>
            <select wire:model.live="statusFilter" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="ALL">Todas las empresas</option>
                <option value="ACTIVE">Solo Activas</option>
                <option value="INACTIVE">Inactivas</option>
            </select>
        </div>
    </div>

    <!-- Employers Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">Empresa / Razón Social</th>
                        <th class="px-5 py-3.5">CUIT</th>
                        <th class="px-5 py-3.5">Empleados</th>
                        <th class="px-5 py-3.5">Banco de Aportes</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employers as $emp)
                        <tr class="hover:bg-slate-50/80 transition {{ $activeEmployerId === $emp->id ? 'bg-indigo-50/30' : '' }}">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900 text-sm">{{ $emp->legal_name }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $emp->currentSettings?->payment_place ?? 'Lugar no especificado' }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono font-medium text-slate-700">
                                {{ $emp->formatted_cuit }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                    👥 {{ $emp->employments_count }} activos
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $emp->currentSettings?->contribution_bank ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($emp->status === 'ACTIVE')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-2 whitespace-nowrap">
                                @if ($activeEmployerId === $emp->id)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                        ✓ En Sesión
                                    </span>
                                @else
                                    <button wire:click="activateEmployer({{ $emp->id }})" 
                                            type="button" 
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 border border-slate-200 transition">
                                        Seleccionar
                                    </button>
                                @endif

                                <button wire:click="openEditModal({{ $emp->id }})" 
                                        type="button" 
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 transition">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                No se encontraron empresas con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $employers->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Employer Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4">
            <form wire:submit="saveEmployer" 
                  class="bg-white rounded-2xl max-w-lg w-full flex flex-col shadow-2xl border border-slate-100 overflow-hidden my-auto" 
                  style="max-height: calc(100vh - 36px); height: min(90vh, 580px);">
                <div class="px-6 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $editingEmployerId ? 'Editar Empresa' : 'Registrar Nueva Empresa' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1">✕</button>
                </div>

                <div class="p-5 space-y-3.5 overflow-y-auto flex-1 text-xs" style="overscroll-behavior: contain;">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Razón Social o Nombre Legal *</label>
                        <input wire:model="legal_name" type="text" placeholder="Ej: COMERCIAL NORTE S.A." 
                               class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                        @error('legal_name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">CUIT (11 dígitos) *</label>
                            <input wire:model="cuit" type="text" placeholder="30-11223344-5" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                            @error('cuit') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Estado</label>
                            <select wire:model="status" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                <option value="ACTIVE">Activo</option>
                                <option value="INACTIVE">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Lugar de Pago (Recibo)</label>
                            <input wire:model="payment_place" type="text" placeholder="San Miguel de Tucumán" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Banco Depósito Aportes</label>
                            <input wire:model="contribution_bank" type="text" placeholder="Banco Macro S.A." 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Notas / Convenio Predeterminado</label>
                        <textarea wire:model="notes" rows="2" placeholder="Observaciones internas del estudio contable..."
                                  class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end space-x-3 shrink-0">
                    <button type="button" wire:click="$set('showModal', false)" 
                            class="px-4 py-1.5 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm">
                        Guardar Empresa
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
