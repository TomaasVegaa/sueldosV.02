<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Convenios, Escalas y Fórmulas Salariales</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configuración de categorías CCT, básicos, no remunerativos y conceptos de liquidación con fórmulas AST.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button wire:click="openCreateAgreementModal" 
                    type="button" 
                    class="inline-flex items-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                + Nuevo Convenio
            </button>
            @if ($activeTab === 'CATEGORIES')
                <button wire:click="openCreateCategoryModal" 
                        type="button" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Categoría
                </button>
            @else
                <button wire:click="openCreateConceptModal" 
                        type="button" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Concepto
                </button>
            @endif
        </div>
    </div>

    <!-- Agreements Tabs & Selector -->
    <div class="flex flex-wrap gap-2">
        @foreach ($agreements as $ag)
            <button wire:click="selectAgreement({{ $ag->id }})" 
                    type="button"
                    class="px-4 py-2.5 rounded-xl text-xs font-medium transition flex items-center space-x-2 {{ $selectedAgreementId === $ag->id ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20 font-semibold' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <span>{{ $ag->name }}</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] {{ $selectedAgreementId === $ag->id ? 'bg-indigo-700/80 text-indigo-100' : 'bg-slate-100 text-slate-500' }}">
                    {{ $ag->categories_count }} cat.
                </span>
            </button>
        @endforeach
    </div>

    @if ($selectedAgreement)
        <!-- Sub-Navigation: Categorías vs Conceptos de Liquidación -->
        <div class="flex items-center space-x-2 border-b border-slate-200 pb-2">
            <button wire:click="setTab('CATEGORIES')" 
                    type="button" 
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center space-x-2 {{ $activeTab === 'CATEGORIES' ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600 hover:bg-slate-100' }}">
                <span>📊 Categorías y Escalas Salariales ({{ count($categories) }})</span>
            </button>
            <button wire:click="setTab('CONCEPTS')" 
                    type="button" 
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center space-x-2 {{ $activeTab === 'CONCEPTS' ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600 hover:bg-slate-100' }}">
                <span>🧮 Conceptos y Fórmulas de Liquidación ({{ count($concepts) }})</span>
            </button>
        </div>

        <!-- Search & Info Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="{{ $activeTab === 'CATEGORIES' ? 'Buscar por código o nombre de categoría...' : 'Buscar por código, concepto o fórmula...' }}" 
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <div class="text-xs text-slate-500">
                Convenio Activo: <strong class="text-slate-800">{{ $selectedAgreement->name }}</strong> (Cód. {{ $selectedAgreement->code }})
            </div>
        </div>

        @if ($activeTab === 'CATEGORIES')
            <!-- Categories and Scales Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3.5 w-24">Cód.</th>
                                <th class="px-5 py-3.5">Categoría Profesional</th>
                                <th class="px-5 py-3.5">Condición</th>
                                <th class="px-5 py-3.5 text-right">Básico Remunerativo</th>
                                <th class="px-5 py-3.5 text-right">No Remunerativo</th>
                                <th class="px-5 py-3.5 text-right">Total Escala</th>
                                <th class="px-5 py-3.5">Vigencia</th>
                                <th class="px-5 py-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($categories as $cat)
                                @php
                                    $scale = $cat->currentScale ?? $cat->latestScale;
                                    $basic = (string) ($scale?->basic_salary ?? '0.00');
                                    $nonTax = (string) ($scale?->non_taxable_salary ?? '0.00');
                                    $total = \App\Support\DecimalFormatter::sum([$basic, $nonTax]);
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-4 font-mono font-bold text-indigo-600">
                                        {{ $cat->code }}
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-slate-900 text-sm">
                                        {{ $cat->name }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                            {{ $cat->condition_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right font-mono font-semibold text-slate-900">
                                        ${{ \App\Support\DecimalFormatter::format($basic) }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-mono font-medium text-emerald-700">
                                        @if (\App\Support\DecimalFormatter::isPositive($nonTax))
                                            ${{ \App\Support\DecimalFormatter::format($nonTax) }}
                                        @else
                                            <span class="text-slate-400 font-normal">$0,00</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right font-mono font-bold text-indigo-900 bg-indigo-50/20">
                                        ${{ \App\Support\DecimalFormatter::format($total) }}
                                    </td>
                                    <td class="px-5 py-4 text-slate-500 text-[11px]">
                                        {{ $scale?->effective_from ? $scale->effective_from->format('d/m/Y') : 'Inicial' }}
                                    </td>
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        <button wire:click="openEditCategoryModal({{ $cat->id }})" 
                                                type="button" 
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                                            Editar Escala
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                        No hay categorías cargadas para este convenio colectivo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Global Concepts Notification Banner -->
            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-indigo-900 shadow-xs">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🌐</span>
                    <div>
                        <p class="font-bold text-slate-900">Catálogo Unificado de Conceptos</p>
                        <p class="text-indigo-700">Los conceptos creados y modificados aplican de forma global a todos los convenios (Comercio, Farmacia, Gastronómicos, etc.).</p>
                    </div>
                </div>
                <a href="{{ route('concepts.index') }}" 
                   class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition shrink-0">
                    Abrir Catálogo Global ↗
                </a>
            </div>

            <!-- Concepts and Formulas Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3.5 w-20">Cód.</th>
                                <th class="px-5 py-3.5">Concepto de Liquidación</th>
                                <th class="px-5 py-3.5">Tipo</th>
                                <th class="px-5 py-3.5">Fórmula AST</th>
                                <th class="px-5 py-3.5">Modo</th>
                                <th class="px-5 py-3.5">Cód. ARCA LSD</th>
                                <th class="px-5 py-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($concepts as $cp)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                        {{ $cp->code }}
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-slate-900">
                                        {{ $cp->name }}
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($cp->type === 'REMUNERATIVO')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                                Remunerativo
                                            </span>
                                        @elseif ($cp->type === 'NO_REMUNERATIVO')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                No Remunerativo
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                                Descuento
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-mono font-bold text-indigo-700 bg-slate-50/50">
                                        <code>{{ $cp->formula }}</code>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        {{ $cp->calculation_mode === 'FORMULA' ? 'Fórmula' : 'Informado' }}
                                    </td>
                                    <td class="px-5 py-4 font-mono font-semibold text-slate-700">
                                        {{ $cp->arca_lsd_code ?? '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        <button wire:click="openEditConceptModal({{ $cp->id }})" 
                                                type="button" 
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 transition">
                                            Editar Concepto
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                        No hay conceptos configurados para este convenio.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    <!-- Create / Edit Category Modal -->
    @if ($showCategoryModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4">
            <form wire:submit="saveCategory" 
                  class="bg-white rounded-2xl max-w-lg w-full flex flex-col shadow-2xl border border-slate-100 overflow-hidden my-auto" 
                  style="max-height: calc(100vh - 36px); height: min(90vh, 640px);">
                <div class="px-6 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $editingCategoryId ? 'Modificar Categoría y Escala Salarial' : 'Nueva Categoría Profesional' }}
                    </h3>
                    <button type="button" wire:click="$set('showCategoryModal', false)" class="text-slate-400 hover:text-slate-600 p-1">✕</button>
                </div>

                <div class="p-5 space-y-3 overflow-y-auto flex-1 text-xs" style="overscroll-behavior: contain;">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Código *</label>
                            <input wire:model="category_code" type="text" placeholder="Ej: 019" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                            @error('category_code') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Denominación *</label>
                            <input wire:model="category_name" type="text" placeholder="Ej: VENDEDOR B" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                            @error('category_name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Condición *</label>
                            <select wire:model="condition" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                <option value="M">M - Mensual</option>
                                <option value="D">D - Diario (Jornal)</option>
                                <option value="H">H - Por Hora</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fecha Vigencia *</label>
                            <input wire:model="effective_from" type="date" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                            @error('effective_from') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl space-y-2.5 border border-slate-200">
                        <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Importes de la Escala Salarial</div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Sueldo Básico ($) *</label>
                                <input wire:model="basic_salary" type="number" step="0.01" placeholder="Ej: 1226349.00" 
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                                @error('basic_salary') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">No Remunerativo ($)</label>
                                <input wire:model="non_taxable_salary" type="number" step="0.01" placeholder="Ej: 140000.00" 
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                                @error('non_taxable_salary') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Observaciones / Acuerdo Paritario</label>
                            <input wire:model="notes" type="text" placeholder="Ej: Acuerdo Salarial Comercio Sep 2026" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end space-x-3 shrink-0">
                    <button type="button" wire:click="$set('showCategoryModal', false)" 
                            class="px-4 py-1.5 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm">
                        Guardar Categoría
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Create / Edit Concept Modal -->
    @if ($showConceptModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4">
            <form wire:submit="saveConcept" 
                  class="bg-white rounded-2xl max-w-xl w-full flex flex-col shadow-2xl border border-slate-100 overflow-hidden my-auto" 
                  style="max-height: calc(100vh - 36px); height: min(90vh, 640px);">
                <div class="px-6 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $editingConceptId ? 'Modificar Concepto de Liquidación' : 'Nuevo Concepto de Liquidación' }}
                    </h3>
                    <button type="button" wire:click="$set('showConceptModal', false)" class="text-slate-400 hover:text-slate-600 p-1">✕</button>
                </div>

                <div class="p-5 space-y-3.5 overflow-y-auto flex-1 text-xs" style="overscroll-behavior: contain;">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Código (3 dígitos) *</label>
                            <input wire:model="concept_code" type="text" placeholder="Ej: 004" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                            @error('concept_code') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Denominación del Concepto *</label>
                            <input wire:model="concept_name" type="text" placeholder="Ej: PRESENTISMO" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                            @error('concept_name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tipo de Haber / Descuento *</label>
                            <select wire:model="concept_type" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white font-medium">
                                <option value="REMUNERATIVO">Remunerativo (Con Aportes)</option>
                                <option value="NO_REMUNERATIVO">No Remunerativo</option>
                                <option value="DESCUENTO">Descuento / Retención</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Modo de Liquidación *</label>
                            <select wire:model="concept_calculation_mode" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                                <option value="FORMULA">1. Calcula según fórmula AST</option>
                                <option value="INFORMED_VALUE">2. Liquida valor informado</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fórmula AST y variables -->
                    <div class="p-3 bg-slate-50 rounded-xl space-y-1.5 border border-slate-200">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-700">Fórmula Matemática AST *</label>
                            <span class="text-[10px] text-slate-500">Parser seguro sin eval</span>
                        </div>
                        <input wire:model="concept_formula" type="text" placeholder="Ej: (SB + LQ002) * 8.3333 / 100" 
                               class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                        @error('concept_formula') <span class="text-rose-600 text-[11px] block">{{ $message }}</span> @enderror

                        <div class="text-[10px] text-slate-500 leading-relaxed bg-white p-2 rounded-lg border border-slate-200">
                            <strong>Variables reconocidas:</strong>
                            <span class="font-mono text-indigo-600">SB</span> (Básico categoría), 
                            <span class="font-mono text-indigo-600">NR</span> (No remunerativo categoría), 
                            <span class="font-mono text-indigo-600">AN</span> (Años de antigüedad), 
                            <span class="font-mono text-indigo-600">TH</span> (Total haberes remunerativos), 
                            <span class="font-mono text-indigo-600">TS</span> (Total haberes no remunerativos), 
                            <span class="font-mono text-indigo-600">LQxxx</span> (Resultado del concepto xxx, ej. LQ002).
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Código Homologado ARCA LSD</label>
                            <input wire:model="concept_arca_lsd_code" type="text" placeholder="Ej: 110000 o 810001" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs">
                        </div>

                        <div class="flex items-center pt-5">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input wire:model="concept_print_on_receipt" type="checkbox" class="h-4 w-4 text-indigo-600 rounded">
                                <span class="font-medium text-slate-700 text-xs">Imprimir en el Recibo de Sueldo</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end space-x-3 shrink-0">
                    <button type="button" wire:click="$set('showConceptModal', false)" 
                            class="px-4 py-1.5 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm">
                        Guardar Concepto
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Create Agreement Modal -->
    @if ($showAgreementModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4">
            <form wire:submit="saveAgreement" 
                  class="bg-white rounded-2xl max-w-md w-full flex flex-col shadow-2xl border border-slate-100 overflow-hidden my-auto" 
                  style="max-height: calc(100vh - 36px);">
                <div class="px-6 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
                    <h3 class="text-base font-bold text-slate-900">Nuevo Convenio Colectivo</h3>
                    <button type="button" wire:click="$set('showAgreementModal', false)" class="text-slate-400 hover:text-slate-600 p-1">✕</button>
                </div>

                <div class="p-5 space-y-3.5 overflow-y-auto flex-1 text-xs" style="overscroll-behavior: contain;">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Código CCT (3 dígitos) *</label>
                        <input wire:model="new_agreement_code" type="text" placeholder="Ej: 003" 
                               class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                        @error('new_agreement_code') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Denominación del Convenio *</label>
                        <input wire:model="new_agreement_name" type="text" placeholder="Ej: Comercio — CCT 130/75" 
                               class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
                        @error('new_agreement_name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Descripción / Notas</label>
                        <textarea wire:model="new_agreement_description" rows="2" 
                                  class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end space-x-3 shrink-0">
                    <button type="button" wire:click="$set('showAgreementModal', false)" 
                            class="px-4 py-1.5 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm">
                        Crear Convenio
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
