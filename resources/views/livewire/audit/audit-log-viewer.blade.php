<div class="space-y-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Registro de Auditoría y Trazabilidad</h1>
            <p class="text-xs text-slate-500 mt-0.5">Historial inmutable de accesos, modificaciones y operaciones por operador y oficina.</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-slate-500">Filtrar Acción:</span>
            <select wire:model.live="actionFilter" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                <option value="ALL">Todas las acciones</option>
                <option value="LOGIN">LOGIN</option>
                <option value="LOGOUT">LOGOUT</option>
                <option value="CREATE">CREATE</option>
                <option value="UPDATE">UPDATE</option>
                <option value="SWITCH_TENANT">SWITCH_TENANT</option>
            </select>
        </div>
    </div>

    <!-- Search Input -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
        <div class="relative w-full max-w-md">
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Buscar por motivo, entidad o IP..." 
                   class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>

    <!-- Audit Events Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5 w-40">Fecha y Hora</th>
                        <th class="px-5 py-3.5">Operador & Oficina</th>
                        <th class="px-5 py-3.5 w-32">Acción</th>
                        <th class="px-5 py-3.5">Entidad Afectada</th>
                        <th class="px-5 py-3.5">Empresa</th>
                        <th class="px-5 py-3.5">Detalle / Motivo</th>
                        <th class="px-5 py-3.5 w-28">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($events as $event)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-mono text-slate-500 text-[11px] whitespace-nowrap">
                                {{ $event->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-900">{{ $event->user?->name ?? 'Sistema' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $event->user?->office ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $badge = match ($event->action) {
                                        'LOGIN' => 'bg-emerald-100 text-emerald-800',
                                        'LOGOUT' => 'bg-slate-100 text-slate-700',
                                        'CREATE' => 'bg-blue-100 text-blue-800',
                                        'UPDATE' => 'bg-amber-100 text-amber-800',
                                        'DELETE' => 'bg-rose-100 text-rose-800',
                                        'SWITCH_TENANT' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-slate-100 text-slate-800',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $badge }}">
                                    {{ $event->action }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-slate-700">
                                {{ $event->entity_type }} #{{ $event->entity_id ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 truncate max-w-[150px]">
                                {{ $event->employer?->legal_name ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $event->reason ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-[11px] text-slate-400">
                                {{ $event->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                No se encontraron registros de auditoría.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($events->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
