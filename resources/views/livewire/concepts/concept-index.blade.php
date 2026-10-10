<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-2xl">⚙️</span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Catálogo Global de Conceptos</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    Transversal a todos los convenios
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500">
                Los conceptos creados y modificados aplican de forma unificada a todos los convenios del estudio (Comercio CCT 130/75, Farmacia, Gastronómicos, Construcción, etc.).
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="openCreateModal" 
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Concepto Global
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Conceptos</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totalCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Catálogo unificado</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                📚
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-emerald-100 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Remunerativos</p>
                <p class="text-2xl font-bold text-emerald-800 mt-1">{{ $remCount }}</p>
                <p class="text-[11px] text-emerald-600/80 mt-0.5">Sujetos a aportes</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                💼
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-sky-100 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-sky-600 uppercase tracking-wider">No Remunerativos</p>
                <p class="text-2xl font-bold text-sky-800 mt-1">{{ $nonTaxCount }}</p>
                <p class="text-[11px] text-sky-600/80 mt-0.5">Acuerdos y sumas fijas</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl">
                ✨
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-rose-100 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Descuentos y Ret.</p>
                <p class="text-2xl font-bold text-rose-800 mt-1">{{ $discCount }}</p>
                <p class="text-[11px] text-rose-600/80 mt-0.5">Jubilación, O.S., sindicatos</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                🛡️
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <!-- Search bar -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Buscar por código (ej: 001, 250, 400), descripción o código ARCA..." 
                   class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-lg shrink-0 overflow-x-auto">
            <button wire:click="$set('typeFilter', 'ALL')" 
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $typeFilter === 'ALL' ? 'bg-white text-indigo-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                Todos ({{ $totalCount }})
            </button>
            <button wire:click="$set('typeFilter', 'REMUNERATIVO')" 
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $typeFilter === 'REMUNERATIVO' ? 'bg-white text-emerald-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                Remunerativos
            </button>
            <button wire:click="$set('typeFilter', 'NO_REMUNERATIVO')" 
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $typeFilter === 'NO_REMUNERATIVO' ? 'bg-white text-sky-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                No Remunerativos
            </button>
            <button wire:click="$set('typeFilter', 'DESCUENTO')" 
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $typeFilter === 'DESCUENTO' ? 'bg-white text-rose-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                Descuentos
            </button>
        </div>
    </div>

    <!-- Table of Concepts -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 divide-y divide-slate-200">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3.5 w-16 text-center">Código</th>
                        <th class="px-4 py-3.5">Descripción del Concepto</th>
                        <th class="px-4 py-3.5">Clasificación</th>
                        <th class="px-4 py-3.5">Fórmula de Cálculo</th>
                        <th class="px-4 py-3.5 text-center">Valor / %</th>
                        <th class="px-4 py-3.5">Condición</th>
                        <th class="px-4 py-3.5 text-center">Cód. ARCA</th>
                        <th class="px-4 py-3.5 text-center">Recibo</th>
                        <th class="px-4 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($concepts as $c)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Code -->
                            <td class="px-4 py-3.5 text-center font-mono font-bold text-slate-900">
                                <span class="px-2 py-1 bg-slate-100 rounded-md border border-slate-200 text-xs">
                                    {{ $c->code }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="px-4 py-3.5 font-medium text-slate-900">
                                <div>{{ $c->name }}</div>
                                @if ($c->is_automatic)
                                    <span class="inline-block text-[10px] text-slate-400 font-normal">Alta automática</span>
                                @endif
                            </td>

                            <!-- Type badge -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($c->type === 'REMUNERATIVO')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Remunerativo
                                    </span>
                                @elseif ($c->type === 'NO_REMUNERATIVO')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                        No Remunerativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        Descuento
                                    </span>
                                @endif
                            </td>

                            <!-- Formula -->
                            <td class="px-4 py-3.5 font-mono text-xs text-indigo-700">
                                <span class="bg-indigo-50/60 px-2 py-0.5 rounded border border-indigo-100">
                                    {{ $c->formula }}
                                </span>
                            </td>

                            <!-- Default amount / % -->
                            <td class="px-4 py-3.5 text-center font-mono text-xs">
                                @if ($c->default_amount !== null)
                                    <span class="font-semibold text-slate-800">
                                        {{ rtrim(rtrim((string)$c->default_amount, '0'), '.') }}
                                        {{ $c->condition_type === 'P' ? '%' : '' }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- Liquidation condition -->
                            <td class="px-4 py-3.5 text-xs text-slate-600 font-mono">
                                @if ($c->liquidation_condition)
                                    <span class="bg-amber-50 text-amber-800 px-2 py-0.5 rounded border border-amber-200 text-[11px]">
                                        {{ $c->liquidation_condition }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px] font-sans">Siempre</span>
                                @endif
                            </td>

                            <!-- ARCA code -->
                            <td class="px-4 py-3.5 text-center font-mono text-xs text-slate-600">
                                @if ($c->arca_lsd_code)
                                    <span class="bg-slate-100 px-2 py-0.5 rounded text-[11px] font-semibold text-slate-700">
                                        {{ $c->arca_lsd_code }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Print on receipt -->
                            <td class="px-4 py-3.5 text-center">
                                @if ($c->print_on_receipt)
                                    <span class="text-emerald-600" title="Imprime en recibo">✓</span>
                                @else
                                    <span class="text-slate-300" title="No imprime">✗</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-2">
                                <button wire:click="editConcept({{ $c->id }})" 
                                        class="px-2.5 py-1 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-md transition">
                                    Editar
                                </button>
                                <button wire:click="toggleStatus({{ $c->id }})" 
                                        class="px-2 py-1 text-xs font-medium {{ $c->status === 'ACTIVE' ? 'text-slate-500 hover:text-rose-600' : 'text-emerald-600 hover:text-emerald-700' }} rounded-md transition">
                                    {{ $c->status === 'ACTIVE' ? 'Desactivar' : 'Activar' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-500">
                                <div class="text-3xl mb-2">🔍</div>
                                <p class="text-base font-semibold text-slate-700">No se encontraron conceptos</p>
                                <p class="text-xs text-slate-400 mt-1">Prueba cambiando los filtros de búsqueda o crea un nuevo concepto.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($concepts->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $concepts->links() }}
            </div>
        @endif
    </div>

    <!-- Reference Guide Card for Variables -->
    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-2">
            <span>ℹ️</span> Guía de Variables para Fórmulas de Liquidación
        </h3>
        <p class="text-xs text-slate-600 mb-4">
            El motor matemático evalúa expresiones con precisión decimal exacta (sin coma flotante). Puedes utilizar las siguientes variables estandarizadas en cualquier concepto:
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 text-xs">
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">SB</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Sueldo Básico de la categoría (prorrateado por jornada).</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">NR</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Suma No Remunerativa pactada de la categoría.</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">TH</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Total de Haberes Remunerativos liquidados en el mes.</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">TS</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Total de Salarios / Conceptos No Remunerativos.</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">TD</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Total de Descuentos acumulados hasta el momento.</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">AN</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Años de antigüedad computables del trabajador.</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">VI</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Valor Informado / Cantidad de unidades o días (default 1).</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">IM</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Importe o porcentaje propio fijado en el concepto.</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">LQxxx</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Importe calculado del concepto con código xxx (ej: LQ002).</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">CQxxx</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Cantidad de días/unidades del concepto xxx (ej: CQ004 inasistencias).</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">OSO</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Código oficial de Obra Social (ej: 126205 para OSECAC).</span>
            </div>
            <div class="p-2.5 bg-white rounded-lg border border-slate-200">
                <code class="font-bold text-indigo-600 font-mono">SIT / SID / SIM</code>
                <span class="block text-[11px] text-slate-500 mt-0.5">Situación de jornada, días trabajados y días del mes.</span>
            </div>
        </div>
    </div>

    <!-- MODAL: Create / Edit Concept -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4">
            <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-auto" style="max-height: 90vh;">
                <!-- Modal Header (Pinned at top) -->
                <div class="px-6 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/90 shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            {{ $editingConceptId ? 'Editar Concepto Global' : 'Nuevo Concepto Global' }}
                        </h3>
                        <p class="text-[11px] text-slate-500">
                            Aplica de manera unificada a todos los convenios colectivos del estudio.
                        </p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 transition p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body (Scrollable if viewport is small) -->
                <div class="p-5 space-y-3.5 overflow-y-auto flex-1 text-xs">
                    <!-- Row 1: Code, Type, Status -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Código del Concepto *</label>
                            <input type="text" 
                                   wire:model="code" 
                                   placeholder="ej: 001, 111, 400" 
                                   class="w-full px-3 py-1.5 text-xs font-mono border rounded-lg focus:ring-2 focus:ring-indigo-500 {{ $errors->has('code') ? 'border-rose-300' : 'border-slate-300' }}" />
                            @error('code') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tipo de Concepto *</label>
                            <select wire:model="type" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="REMUNERATIVO">Remunerativo (con aportes)</option>
                                <option value="NO_REMUNERATIVO">No Remunerativo</option>
                                <option value="DESCUENTO">Descuento / Aporte Ley</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Estado</label>
                            <select wire:model="status" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="ACTIVE">Activo</option>
                                <option value="INACTIVE">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Name & Calculation Mode -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Descripción del Concepto *</label>
                            <input type="text" 
                                   wire:model="name" 
                                   placeholder="ej: SUELDO BASICO, PRESENTISMO, JUBILACION (SIPA 11%)" 
                                   class="w-full px-3 py-1.5 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500 {{ $errors->has('name') ? 'border-rose-300' : 'border-slate-300' }}" />
                            @error('name') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Modo de Cálculo</label>
                            <select wire:model="calculation_mode" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="FORMULA">Por Fórmula AST</option>
                                <option value="INFORMED_VALUE">Valor Informado (VI)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3: Formula & Default Amount -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">
                                Fórmula Matemática * 
                                <span class="text-[11px] font-normal text-slate-400">(ej: SB * VI / 30, TH * IM / 100, NR)</span>
                            </label>
                            <input type="text" 
                                   wire:model="formula" 
                                   placeholder="ej: SB * VI / 30 * 1" 
                                   class="w-full px-3 py-1.5 text-xs font-mono border rounded-lg focus:ring-2 focus:ring-indigo-500 {{ $errors->has('formula') ? 'border-rose-300' : 'border-slate-300' }}" />
                            @error('formula') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Importe / % Propio (IM)
                                <span class="text-[11px] text-slate-400 font-normal">(ej: 11, 3, 0.5)</span>
                            </label>
                            <input type="number" 
                                   step="0.0001" 
                                   wire:model="default_amount" 
                                   placeholder="ej: 11.0000" 
                                   class="w-full px-3 py-1.5 text-xs font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <!-- Row 4: Condition, Condition Type & ARCA Code -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Condición para Liquidar
                                <span class="text-[10px] text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input type="text" 
                                   wire:model="liquidation_condition" 
                                   placeholder="ej: CQ004<0.5, OSO=126205" 
                                   class="w-full px-3 py-1.5 text-xs font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tipo de Base</label>
                            <select wire:model="condition_type" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="P">P - Porcentual / Proporcional</option>
                                <option value="U">U - Unidades / Días trabajados</option>
                                <option value="T">T - Todos / Monto Fijo</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Cód. ARCA Libro Sueldos (LSD)
                            </label>
                            <input type="text" 
                                   wire:model="arca_lsd_code" 
                                   placeholder="ej: 110000, 810001" 
                                   class="w-full px-3 py-1.5 text-xs font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <!-- Row 5: Order & Checkboxes -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Orden de Liquidación</label>
                            <input type="number" 
                                   wire:model="order" 
                                   placeholder="10, 20, 100..." 
                                   class="w-full px-3 py-1.5 text-xs font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                        </div>

                        <div class="sm:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-2 pt-4">
                            <label class="flex items-center space-x-1.5 text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="print_on_receipt" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                <span>En Recibo</span>
                            </label>
                            <label class="flex items-center space-x-1.5 text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="print_quantity" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                <span>Cantidad</span>
                            </label>
                            <label class="flex items-center space-x-1.5 text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="print_unit_price" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                <span>P. Unitario</span>
                            </label>
                            <label class="flex items-center space-x-1.5 text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="is_automatic" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                <span>Automático</span>
                            </label>
                        </div>
                    </div>

                    <!-- Interactive Formula Sandbox / Tester Toggle -->
                    <div class="border border-indigo-100 rounded-xl bg-indigo-50/40 p-3 mt-1">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-indigo-900 flex items-center gap-1.5">
                                <span>🧪</span> Simulador y Verificador de Fórmula
                            </span>
                            <button type="button" 
                                    wire:click="$toggle('showTester')" 
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold underline text-[11px]">
                                {{ $showTester ? 'Ocultar simulador' : 'Probar fórmula con valores' }}
                            </button>
                        </div>

                        @if ($showTester)
                            <div class="mt-2.5 pt-2.5 border-t border-indigo-100 space-y-2.5">
                                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 text-[11px]">
                                    <div>
                                        <label class="block text-[10px] text-slate-500">SB (Básico)</label>
                                        <input type="text" wire:model="test_sb" class="w-full px-2 py-1 text-xs font-mono border rounded bg-white" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-500">AN (Años)</label>
                                        <input type="text" wire:model="test_an" class="w-full px-2 py-1 text-xs font-mono border rounded bg-white" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-500">VI (Unid/Días)</label>
                                        <input type="text" wire:model="test_vi" class="w-full px-2 py-1 text-xs font-mono border rounded bg-white" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-500">IM (Importe)</label>
                                        <input type="text" wire:model="test_im" class="w-full px-2 py-1 text-xs font-mono border rounded bg-white" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-500">TH (Total Rem)</label>
                                        <input type="text" wire:model="test_th" class="w-full px-2 py-1 text-xs font-mono border rounded bg-white" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-500">TS (No Rem)</label>
                                        <input type="text" wire:model="test_ts" class="w-full px-2 py-1 text-xs font-mono border rounded bg-white" />
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button type="button" 
                                            wire:click="testCurrentFormula" 
                                            class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-xs font-medium hover:bg-indigo-700 transition">
                                        Calcular
                                    </button>

                                    @if ($test_result)
                                        <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                            Resultado exacto: {{ $test_result }}
                                        </span>
                                    @endif

                                    @if ($test_error)
                                        <span class="text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                                            {{ $test_error }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal Footer (Pinned at bottom) -->
                <div class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-3 shrink-0">
                    <button wire:click="$set('showModal', false)" 
                            type="button" 
                            class="px-4 py-1.5 text-xs font-medium text-slate-700 hover:text-slate-900 transition">
                        Cancelar
                    </button>
                    <button wire:click="saveConcept" 
                            type="button" 
                            class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs transition">
                        Guardar Concepto Global
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
