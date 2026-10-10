<div class="space-y-6">
    <!-- Active Employer Info Banner -->
    @if ($activeEmployer)
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-5 rounded-2xl border border-slate-800 text-white shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs text-indigo-400 font-semibold tracking-wide uppercase">Nómina de Personal de la Empresa:</span>
                <h1 class="text-xl font-bold text-white tracking-tight mt-0.5">{{ $activeEmployer->legal_name }}</h1>
                <div class="text-xs text-slate-300 mt-1 font-mono">CUIT: {{ $activeEmployer->formatted_cuit }}</div>
            </div>
            <button wire:click="openCreateModal" 
                    type="button" 
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-md transition shrink-0">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Legajo
            </button>
        </div>
    @else
        <div class="p-6 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-sm">
            <span class="font-bold">⚠️ Atención:</span> Debe seleccionar una empresa activa en la barra superior para gestionar sus legajos.
        </div>
    @endif

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-96">
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Buscar por Legajo, Nombre o CUIL..." 
                   class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <div class="flex items-center space-x-2 w-full md:w-auto">
            <span class="text-xs text-slate-500">Estado:</span>
            <select wire:model.live="statusFilter" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="ALL">Todos los estados</option>
                <option value="ACTIVE">Activos</option>
                <option value="TERMINATED">Baja / Desvinculados</option>
            </select>
        </div>
    </div>

    <!-- Workforce Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">Legajo</th>
                        <th class="px-5 py-3.5">Empleado & CUIL</th>
                        <th class="px-5 py-3.5">Ingreso / Antigüedad</th>
                        <th class="px-5 py-3.5">CCT / Categoría</th>
                        <th class="px-5 py-3.5">Jornada</th>
                        <th class="px-5 py-3.5">Sueldo Pactado</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employments as $emp)
                        @php
                            $version = $emp->currentVersion ?? $emp->latestVersion;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                #{{ $emp->file_number }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900 text-sm">{{ $emp->worker->full_name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">CUIL: {{ $emp->worker->formatted_cuil }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <div>{{ $emp->hire_date->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-400">Antig.: {{ $emp->seniority_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $version?->category_name ?? '—' }}</div>
                                <div class="text-[11px] text-slate-400">CCT: {{ $version?->agreement_code ?? '003' }} (Cat: {{ $version?->category_code }})</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $version?->workday_label ?? 'Completa' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-mono font-semibold text-slate-900">
                                @if ($version?->agreed_salary)
                                    ${{ number_format($version->agreed_salary, 2, ',', '.') }}
                                @else
                                    <span class="text-slate-400 font-normal">Escala CCT</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($emp->status === 'ACTIVE')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">
                                        Baja
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <button wire:click="openEditModal({{ $emp->id }})" 
                                        type="button" 
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 transition">
                                    Editar Legajo
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                No se encontraron legajos registrados para esta empresa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $employments->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Employment Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4">
            <form wire:submit="saveEmployment" 
                  class="bg-white rounded-2xl max-w-2xl w-full flex flex-col shadow-2xl border border-slate-100 overflow-hidden my-auto" 
                  style="max-height: calc(100vh - 36px); height: min(90vh, 680px);">
                <div class="px-6 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            {{ $editingEmploymentId ? 'Editar Legajo de Personal' : 'Alta de Nuevo Legajo' }}
                        </h3>
                        <p class="text-xs text-slate-500">Empresa: {{ $activeEmployer?->legal_name }}</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1">✕</button>
                </div>

                <div class="p-5 space-y-3.5 overflow-y-auto flex-1 text-xs" style="overscroll-behavior: contain;">
                        <!-- Datos Personales -->
                        <div class="p-3 bg-slate-50 rounded-xl space-y-2.5 border border-slate-200/80">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Datos del Trabajador</div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">CUIL (11 dígitos) *</label>
                                    <input wire:model="cuil" type="text" placeholder="20-12345678-9" 
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                                    @error('cuil') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Apellido *</label>
                                    <input wire:model="last_name" type="text" placeholder="Gómez" 
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                                    @error('last_name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Nombres *</label>
                                    <input wire:model="first_name" type="text" placeholder="Juan Carlos" 
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                                    @error('first_name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Datos del Empleo -->
                        <div class="p-3 bg-slate-50 rounded-xl space-y-2.5 border border-slate-200/80">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Condiciones de Empleo y Fechas</div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">N° Legajo *</label>
                                    <input wire:model="file_number" type="text" placeholder="0001" 
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                                    @error('file_number') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Fecha Ingreso *</label>
                                    <input wire:model="hire_date" type="date" 
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                                    @error('hire_date') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Fecha Antigüedad *</label>
                                    <input wire:model="seniority_date" type="date" 
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                                    @error('seniority_date') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Estado</label>
                                    <select wire:model="employment_status" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                        <option value="ACTIVE">Activo</option>
                                        <option value="TERMINATED">Baja</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Parámetros de Convenio, Categoría y Escala Salarial -->
                        <div class="p-3 bg-slate-50 rounded-xl space-y-2.5 border border-slate-200/80">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Convenio, Categoría y Escala Salarial</div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Convenio Colectivo (CCT) *</label>
                                    <select wire:model.live="agreement_code" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white font-medium">
                                        @foreach ($agreements as $ag)
                                            <option value="{{ $ag->code }}">{{ $ag->code }} - {{ $ag->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block font-semibold text-slate-700">Categoría Profesional *</label>
                                        <a href="{{ route('agreements.index') }}" target="_blank" class="text-[10px] text-indigo-600 hover:underline">+ Administrar</a>
                                    </div>
                                    <select wire:model.live="category_code" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white font-medium">
                                        @foreach ($availableCategories as $c)
                                            <option value="{{ $c->code }}">{{ $c->code }} - {{ $c->name }} ({{ $c->condition_label }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Panel Informativo de Escala de la Categoría Seleccionada -->
                            <div class="p-2.5 rounded-xl bg-indigo-50/60 border border-indigo-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs">
                                <div class="space-y-0.5">
                                    <div class="font-bold text-slate-800 flex items-center space-x-1.5">
                                        <span class="text-indigo-600 font-mono">{{ $category_code }}</span>
                                        <span>{{ $category_name }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 flex items-center space-x-3">
                                        <span>Básico: <strong class="font-mono text-slate-900">${{ number_format($scale_basic, 2, ',', '.') }}</strong></span>
                                        @if ($scale_non_taxable > 0)
                                            <span>No Remun.: <strong class="font-mono text-emerald-700">${{ number_format($scale_non_taxable, 2, ',', '.') }}</strong></span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-[10px] text-slate-500 block">Sugerido p/ Jornada:</span>
                                    <span class="font-mono font-bold text-indigo-700 text-sm">${{ number_format($suggested_basic, 2, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Tipo de Jornada *</label>
                                    <select wire:model.live="workday_type" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                        <option value="1">Jornada Completa (100%)</option>
                                        <option value="2">3/4 Jornada (75%)</option>
                                        <option value="3">Media Jornada (50%)</option>
                                        <option value="4">1/4 Jornada (25%)</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block font-semibold text-slate-700 mb-1">Obra Social (Catálogo ARCA) *</label>
                                    <select wire:model="health_insurer_code" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                        @foreach ($obrasSociales as $os)
                                            <option value="{{ $os->code }}">{{ $os->code }} - {{ $os->description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">CBU (Para Recibo de Sueldo)</label>
                                    <input wire:model="cbu" type="text" placeholder="22 dígitos" maxlength="22"
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs">
                                    @error('cbu') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block font-semibold text-slate-700">Sueldo Básico Pactado ($)</label>
                                        <button type="button" wire:click="$set('agreed_salary', '{{ $suggested_basic }}')" class="text-[10px] text-indigo-600 hover:underline">
                                            Usar Escala
                                        </button>
                                    </div>
                                    <input wire:model="agreed_salary" type="number" step="0.01" placeholder="Ej: 613174.50"
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs">
                                    @error('agreed_salary') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end space-x-3 shrink-0">
                        <button type="button" wire:click="$set('showModal', false)" 
                                class="px-4 py-1.5 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm">
                            Guardar Legajo
                        </button>
                    </div>
                </form>
            </div>
    @endif
</div>
