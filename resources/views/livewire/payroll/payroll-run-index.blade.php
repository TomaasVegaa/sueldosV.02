<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Liquidaciones y Recibos</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                    CCT 130/75 Comercio
                </span>
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                Cálculo salarial multiempresa con motor AST de precisión decimal, versionado inmutable y recibos legales (art. 140 LCT).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="openNewRunModal" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva Liquidación</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start justify-between text-emerald-800 text-sm">
            <div class="flex items-center gap-2">
                <span class="text-lg">✅</span>
                <span>{{ $successMessage }}</span>
            </div>
            <button wire:click="$set('successMessage', null)" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
        </div>
    @endif

    @if($errorMessage)
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl flex items-start justify-between text-rose-800 text-sm">
            <div class="flex items-center gap-2">
                <span class="text-lg">⚠️</span>
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
        </div>
    @endif

    @if($periods->isEmpty())
        <!-- Empty State -->
        <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 shadow-xs">
            <div class="w-16 h-16 mx-auto mb-4 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl">
                📑
            </div>
            <h3 class="text-lg font-bold text-slate-800">No hay liquidaciones generadas</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto mt-1 mb-6">
                Comience creando el primer período mensual para la empresa activa y calcule los recibos para su nómina de Comercio.
            </p>
            <button wire:click="openNewRunModal" 
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Crear Período y Liquidación
            </button>
        </div>
    @else
        <!-- Period & Run Selector Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Período:</label>
                <select wire:change="selectPeriod($event.target.value)" 
                        class="text-sm font-semibold rounded-lg border-slate-300 text-slate-800 focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ $currentPeriod?->id === $p->id ? 'selected' : '' }}>
                            {{ $p->formatted_period }} ({{ $p->year_month }})
                        </option>
                    @endforeach
                </select>

                @if($runs->count() > 1)
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-2">Corrida:</label>
                    <select wire:change="selectRun($event.target.value)" 
                            class="text-sm font-semibold rounded-lg border-slate-300 text-slate-800 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($runs as $r)
                            <option value="{{ $r->id }}" {{ $currentRun?->id === $r->id ? 'selected' : '' }}>
                                {{ $r->run_type_label }} N.° {{ $r->run_number }} ({{ $r->pay_date->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            @if($currentRun)
                <div class="flex items-center gap-2">
                    <button wire:click="openNoveltiesModal({{ $currentRun->id }})" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 transition">
                        <span>📝 Novedades y Días</span>
                    </button>

                    <button wire:click="calculateRun({{ $currentRun->id }})" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                        <span wire:loading.remove wire:target="calculateRun({{ $currentRun->id }})">⚡ {{ $currentVersion ? 'Recalcular Lote' : 'Calcular Lote' }}</span>
                        <span wire:loading wire:target="calculateRun({{ $currentRun->id }})">Calculando...</span>
                    </button>

                    @if($currentVersion && $payslips->isNotEmpty())
                        <a href="{{ route('payroll.batch.pdf', $currentVersion->id) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-lg border border-rose-200 transition">
                            <span>📄 Descargar Todos (PDF)</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($currentRun)
            <!-- Status & Run Overview Card -->
            <div class="bg-slate-900 text-white p-5 rounded-2xl shadow-sm border border-slate-800">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                {{ $currentRun->run_type_label }}
                            </span>
                            <span class="text-sm font-bold text-slate-200">
                                Período {{ $currentPeriod->formatted_period }} — Fecha de Pago: {{ $currentRun->pay_date->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-400 mt-2">
                            <span>Empleados en Nómina: <strong class="text-slate-200">{{ $activeEmployeesCount }}</strong></span>
                            <span>&bull;</span>
                            <span>Días Base: <strong class="text-slate-200">{{ $currentRun->base_days }}</strong></span>
                            <span>&bull;</span>
                            <span>Estado: 
                                <span class="font-bold {{ $currentRun->status === 'CALCULATED' ? 'text-emerald-400' : 'text-amber-400' }}">
                                    {{ $currentRun->status }}
                                </span>
                            </span>
                        </div>
                    </div>

                    @if($currentVersion)
                        <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700 text-right">
                            <div class="text-[11px] text-slate-400 font-mono flex items-center justify-end gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Versión Activa: <strong class="text-emerald-400">v{{ $currentVersion->version_number }}</strong></span>
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5" title="SHA-256 Inmutable Snapshot">
                                SHA: <span class="text-slate-300">{{ substr($currentVersion->snapshot_sha256, 0, 16) }}...</span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                Calculado: {{ $currentVersion->calculated_at ? $currentVersion->calculated_at->format('d/m/Y H:i:s') : '-' }}
                            </div>
                        </div>
                    @endif
                </div>

                @if($payslips->isNotEmpty())
                    <!-- Financial Metrics Totals Bar -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mt-5 pt-5 border-t border-slate-800">
                        <div class="bg-slate-800/50 p-2.5 rounded-lg border border-slate-700/60">
                            <div class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Bruto Remunerativo</div>
                            <div class="text-base font-bold text-slate-100 font-mono mt-0.5">
                                $ {{ number_format($payslips->sum('gross_remunerative'), 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-slate-800/50 p-2.5 rounded-lg border border-slate-700/60">
                            <div class="text-[10px] font-bold uppercase text-sky-400 tracking-wider">No Remunerativo</div>
                            <div class="text-base font-bold text-sky-300 font-mono mt-0.5">
                                $ {{ number_format($payslips->sum('gross_non_remunerative'), 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-slate-800/50 p-2.5 rounded-lg border border-slate-700/60">
                            <div class="text-[10px] font-bold uppercase text-rose-400 tracking-wider">Retenciones / Desc.</div>
                            <div class="text-base font-bold text-rose-300 font-mono mt-0.5">
                                $ {{ number_format($payslips->sum('total_deductions'), 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-emerald-950/40 p-2.5 rounded-lg border border-emerald-800/60">
                            <div class="text-[10px] font-bold uppercase text-emerald-400 tracking-wider">Neto Total a Cobrar</div>
                            <div class="text-base font-bold text-emerald-300 font-mono mt-0.5">
                                $ {{ number_format($payslips->sum('net_salary'), 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-slate-800/50 p-2.5 rounded-lg border border-slate-700/60">
                            <div class="text-[10px] font-bold uppercase text-indigo-400 tracking-wider">Patronales Estimadas</div>
                            <div class="text-base font-bold text-indigo-300 font-mono mt-0.5">
                                $ {{ number_format($payslips->sum('employer_contributions_total'), 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Payslips Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span>Detalle de Recibos de Haberes</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-normal">
                            {{ $payslips->count() }} recibo(s)
                        </span>
                    </h3>
                </div>

                @if($payslips->isEmpty())
                    <div class="p-8 text-center text-slate-500 text-sm">
                        Esta corrida no tiene recibos calculados todavía. Haga clic en <strong>⚡ Calcular Lote</strong> para liquidar la nómina.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                                    <th class="py-3 px-4">Legajo</th>
                                    <th class="py-3 px-4">Trabajador</th>
                                    <th class="py-3 px-4">CUIL</th>
                                    <th class="py-3 px-4">Categoría CCT</th>
                                    <th class="py-3 px-4 text-right">Hab. Remunerativo</th>
                                    <th class="py-3 px-4 text-right">No Remunerativo</th>
                                    <th class="py-3 px-4 text-right">Descuentos</th>
                                    <th class="py-3 px-4 text-right">Sueldo Neto</th>
                                    <th class="py-3 px-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach($payslips as $slip)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $slip->file_number }}</td>
                                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $slip->worker_name }}</td>
                                        <td class="py-3 px-4 font-mono text-slate-500">{{ $slip->worker_cuil }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $slip->category_code }} - {{ $slip->category_name }}</td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-800">
                                            $ {{ number_format($slip->gross_remunerative, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-sky-700">
                                            $ {{ number_format($slip->gross_non_remunerative, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-rose-700">
                                            $ {{ number_format($slip->total_deductions, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700 text-sm">
                                            $ {{ number_format($slip->net_salary, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button wire:click="inspectPayslip({{ $slip->id }})" 
                                                        class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold rounded text-[11px] transition">
                                                    Ver Recibo
                                                </button>
                                                <a href="{{ route('payroll.payslip.pdf', $slip->id) }}" target="_blank"
                                                   class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded text-[11px] transition" title="Ver PDF">
                                                    PDF
                                                </a>
                                                <a href="{{ route('payroll.payslip.download', $slip->id) }}"
                                                   class="p-1 text-slate-400 hover:text-indigo-600 transition" title="Descargar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    @endif

    <!-- MODAL 1: NUEVA LIQUIDACIÓN -->
    @if($showNewRunModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <form wire:submit.prevent="createRun" 
                  class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col border border-slate-200"
                  style="height: min(90vh, 520px); max-height: calc(100vh - 36px);">
                
                <!-- Pinned Header -->
                <div class="p-5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Nueva Corrida de Liquidación</h3>
                        <p class="text-xs text-slate-500">Apertura de período y lote de liquidación</p>
                    </div>
                    <button type="button" wire:click="$set('showNewRunModal', false)" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
                </div>

                <!-- Scrollable Body -->
                <div class="p-6 space-y-4 overflow-y-auto min-h-0" style="overscroll-behavior: contain;">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Período (Año y Mes - AAAAMM)</label>
                        <input type="text" wire:model="new_year_month" placeholder="Ej: 202609" 
                               class="w-full text-sm rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                        @error('new_year_month') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipo de Liquidación</label>
                        <select wire:model="new_run_type" class="w-full text-sm rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="M">Mensual (Haberes Normales)</option>
                            <option value="Q1">1.ª Quincena</option>
                            <option value="Q2">2.ª Quincena</option>
                            <option value="SAC1">1.er SAC (Junio)</option>
                            <option value="SAC2">2.do SAC (Diciembre)</option>
                            <option value="VAC">Vacaciones</option>
                            <option value="FIN">Liquidación Final / Indemnizatoria</option>
                        </select>
                        @error('new_run_type') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fecha de Pago</label>
                            <input type="date" wire:model="new_pay_date" 
                                   class="w-full text-sm rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('new_pay_date') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Días Base</label>
                            <input type="number" wire:model="new_base_days" min="1" max="31" 
                                   class="w-full text-sm rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('new_base_days') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Notas / Observaciones (Opcional)</label>
                        <textarea wire:model="new_notes" rows="2" placeholder="Notas internas sobre la liquidación..." 
                                  class="w-full text-sm rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                </div>

                <!-- Pinned Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 shrink-0">
                    <button type="button" wire:click="$set('showNewRunModal', false)" 
                            class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 rounded-lg transition">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition">
                        Crear Liquidación
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- MODAL 2: NOVEDADES Y ASISTENCIA -->
    @if($showNoveltiesModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <form wire:submit.prevent="saveNovelties" 
                  class="bg-white rounded-2xl shadow-xl w-full max-w-3xl overflow-hidden flex flex-col border border-slate-200"
                  style="height: min(90vh, 650px); max-height: calc(100vh - 36px);">
                
                <!-- Pinned Header -->
                <div class="p-5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Novedades y Asistencia del Período</h3>
                        <p class="text-xs text-slate-500">Días trabajados, inasistencias y horas extras por empleado</p>
                    </div>
                    <button type="button" wire:click="$set('showNoveltiesModal', false)" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
                </div>

                <!-- Scrollable Body -->
                <div class="p-6 space-y-4 overflow-y-auto min-h-0" style="overscroll-behavior: contain;">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                                    <th class="py-2.5 px-3">Legajo</th>
                                    <th class="py-2.5 px-3">Empleado</th>
                                    <th class="py-2.5 px-3 text-center">Días Trab.</th>
                                    <th class="py-2.5 px-3 text-center">Inasistencias</th>
                                    <th class="py-2.5 px-3 text-center">Hs. Extras 50%</th>
                                    <th class="py-2.5 px-3 text-center">Hs. Extras 100%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($novelties as $empId => $data)
                                    @php $emp = \App\Models\Employment::with('worker')->find($empId); @endphp
                                    <tr>
                                        <td class="py-2.5 px-3 font-mono font-bold">{{ $emp?->file_number }}</td>
                                        <td class="py-2.5 px-3 font-medium text-slate-900">{{ $emp?->worker->full_name }}</td>
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="number" wire:model.defer="novelties.{{ $empId }}.worked_days" min="0" max="31" 
                                                   class="w-16 text-center text-xs rounded border-slate-300 py-1 font-mono">
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="number" wire:model.defer="novelties.{{ $empId }}.absence_days" min="0" max="31" 
                                                   class="w-16 text-center text-xs rounded border-slate-300 py-1 font-mono text-rose-600 font-semibold">
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="number" step="0.5" wire:model.defer="novelties.{{ $empId }}.overtime_50_hours" min="0" 
                                                   class="w-16 text-center text-xs rounded border-slate-300 py-1 font-mono">
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="number" step="0.5" wire:model.defer="novelties.{{ $empId }}.overtime_100_hours" min="0" 
                                                   class="w-16 text-center text-xs rounded border-slate-300 py-1 font-mono">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pinned Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 shrink-0">
                    <button type="button" wire:click="$set('showNoveltiesModal', false)" 
                            class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 rounded-lg transition">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition">
                        Guardar Novedades
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- MODAL 3: INSPECTOR DE RECIBO DE HABERES -->
    @if($showPayslipModal && $inspectingSlip)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl overflow-hidden flex flex-col border border-slate-200"
                 style="height: min(90vh, 720px); max-height: calc(100vh - 36px);">
                
                <!-- Pinned Header -->
                <div class="p-5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50/50">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900">
                                Recibo de Haberes — Legajo {{ $inspectingSlip->file_number }}: {{ $inspectingSlip->worker_name }}
                            </h3>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">
                                Liquidado
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">
                            CUIL: {{ $inspectingSlip->worker_cuil }} &bull; Categoría: {{ $inspectingSlip->category_code }} - {{ $inspectingSlip->category_name }} &bull; Antigüedad: {{ $inspectingSlip->seniority_years }} años
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('payroll.payslip.pdf', $inspectingSlip->id) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                            <span>📄 Ver PDF</span>
                        </a>
                        <a href="{{ route('payroll.payslip.download', $inspectingSlip->id) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                            <span>⬇️ Descargar</span>
                        </a>
                        <button type="button" wire:click="closePayslipModal" class="text-slate-400 hover:text-slate-600 font-bold text-xl ml-2">&times;</button>
                    </div>
                </div>

                <!-- Scrollable Body -->
                <div class="p-6 space-y-5 overflow-y-auto min-h-0" style="overscroll-behavior: contain;">
                    <!-- Table of Calculated Lines -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase border-b border-slate-200">
                                    <th class="py-2.5 px-3 text-center">Cód.</th>
                                    <th class="py-2.5 px-3">Concepto Liquidado</th>
                                    <th class="py-2.5 px-3 text-center">Cantidad</th>
                                    <th class="py-2.5 px-3 text-right">Hab. Remunerativo</th>
                                    <th class="py-2.5 px-3 text-right">No Remunerativo</th>
                                    <th class="py-2.5 px-3 text-right">Descuentos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono">
                                @foreach($inspectingSlip->lines as $line)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-2 px-3 text-center font-bold text-slate-500">{{ $line->concept_code }}</td>
                                        <td class="py-2 px-3 font-sans font-medium text-slate-800">
                                            {{ $line->concept_name }}
                                            @if($line->calculation_trace)
                                                <span class="block text-[10px] text-slate-400 font-mono">{{ $line->calculation_trace }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 text-center text-slate-600">
                                            {{ $line->quantity ? number_format($line->quantity, 2, ',', '.') . ($line->unit ? ' ' . $line->unit : '') : '-' }}
                                        </td>
                                        <td class="py-2 px-3 text-right text-slate-900">
                                            {{ $line->remunerative_amount > 0 ? '$ ' . number_format($line->remunerative_amount, 2, ',', '.') : '' }}
                                        </td>
                                        <td class="py-2 px-3 text-right text-sky-700">
                                            {{ $line->non_taxable_amount > 0 ? '$ ' . number_format($line->non_taxable_amount, 2, ',', '.') : '' }}
                                        </td>
                                        <td class="py-2 px-3 text-right text-rose-700 font-semibold">
                                            {{ $line->deduction_amount > 0 ? '$ ' . number_format($line->deduction_amount, 2, ',', '.') : '' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 font-mono font-bold text-xs border-t border-slate-200">
                                <tr>
                                    <td colspan="3" class="py-2.5 px-3 text-right text-slate-600 uppercase font-sans">Subtotales:</td>
                                    <td class="py-2.5 px-3 text-right text-slate-900">
                                        $ {{ number_format($inspectingSlip->gross_remunerative, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-sky-700">
                                        $ {{ number_format($inspectingSlip->gross_non_remunerative, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-rose-700">
                                        $ {{ number_format($inspectingSlip->total_deductions, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Net Salary Banner -->
                    <div class="p-4 bg-emerald-50 border border-emerald-300 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Sueldo Neto a Cobrar:</span>
                            <div class="text-xs text-emerald-700 italic mt-0.5">
                                {{ $inspectingSlip->net_salary_words }}
                            </div>
                        </div>
                        <div class="text-2xl font-bold font-mono text-emerald-900">
                            $ {{ number_format($inspectingSlip->net_salary, 2, ',', '.') }}
                        </div>
                    </div>

                    <!-- ARCA Bases & Contributions Card -->
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-xs text-slate-600">
                        <div class="font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-2">Bases Imponibles ARCA (Libro de Sueldos Digital)</div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono">
                            @foreach($inspectingSlip->bases as $b)
                                <div class="bg-white p-2 rounded border border-slate-200">
                                    <span class="text-[10px] text-slate-400 block">{{ $b->base_code }}</span>
                                    <span class="font-bold text-slate-800">$ {{ number_format($b->amount, 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                            <div class="bg-white p-2 rounded border border-slate-200">
                                <span class="text-[10px] text-indigo-500 font-bold block">Contribuciones Patronales</span>
                                <span class="font-bold text-indigo-700">$ {{ number_format($inspectingSlip->employer_contributions_total, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pinned Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end shrink-0">
                    <button type="button" wire:click="closePayslipModal" 
                            class="px-5 py-2 text-sm bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
