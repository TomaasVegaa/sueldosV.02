<div class="space-y-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Catálogos Oficiales ARCA (ex-AFIP)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tablas de homologación para Libro de Sueldos Digital (LSD), Obras Sociales y situaciones de revista.</p>
        </div>
        <div class="flex items-center space-x-1.5 bg-slate-100 p-1 rounded-xl">
            <button wire:click="setType('OBRA_SOCIAL')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $typeFilter === 'OBRA_SOCIAL' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                🏥 Obras Sociales ({{ \App\Models\ArcaCatalog::where('catalog_type', 'OBRA_SOCIAL')->count() }})
            </button>
            <button wire:click="setType('MODALIDAD')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $typeFilter === 'MODALIDAD' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                📋 Modalidades
            </button>
            <button wire:click="setType('SIT_REVISTA')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $typeFilter === 'SIT_REVISTA' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                📑 Sit. Revista
            </button>
        </div>
    </div>

    <!-- Search Input -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div class="relative w-full max-w-md">
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Buscar por código numérico o denominación..." 
                   class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <span class="text-xs text-slate-400">Total registros: {{ $items->total() }}</span>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5 w-32">Código ARCA</th>
                        <th class="px-5 py-3.5">Denominación Oficial</th>
                        <th class="px-5 py-3.5 w-40">Tipo Catálogo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-indigo-600">
                                {{ $item->code }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">
                                {{ $item->description }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $item->catalog_type }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-slate-400">
                                No se encontraron registros con los criterios de búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
