<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    @if ($activeEmployer)
        <button @click="open = !open" 
                type="button" 
                class="flex items-center space-x-2.5 bg-slate-800/90 hover:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700/80 text-left transition text-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <div class="max-w-[200px] truncate">
                <span class="text-slate-400 text-[10px] block leading-none">Empresa Activa:</span>
                <span class="font-semibold text-slate-100 truncate block">{{ $activeEmployer->legal_name }}</span>
            </div>
            <svg class="w-4 h-4 text-slate-400 ml-1 transition transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    @else
        <button @click="open = !open" 
                type="button" 
                class="bg-amber-500/20 text-amber-300 border border-amber-500/30 px-3 py-1.5 rounded-lg text-xs font-medium flex items-center space-x-2">
            <span>⚠️ Seleccionar Empresa</span>
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    @endif

    <!-- Dropdown Menu -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute left-0 mt-2 w-80 rounded-xl bg-slate-900 border border-slate-800 shadow-2xl py-2 z-50 text-xs">
        <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">
            Cambiar Empresa de Trabajo
        </div>
        <div class="max-h-64 overflow-y-auto divide-y divide-slate-800/60">
            @forelse ($employers as $emp)
                <button wire:click="selectEmployer({{ $emp->id }})" 
                        @click="open = false" 
                        type="button" 
                        class="w-full text-left px-3 py-2.5 hover:bg-slate-800 flex items-center justify-between transition {{ $activeEmployer?->id === $emp->id ? 'bg-indigo-950/60 text-indigo-300' : 'text-slate-200' }}">
                    <div>
                        <div class="font-medium truncate max-w-[220px]">{{ $emp->legal_name }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">CUIT: {{ $emp->formatted_cuit }}</div>
                    </div>
                    @if ($activeEmployer?->id === $emp->id)
                        <span class="text-indigo-400 text-sm font-bold">✓</span>
                    @endif
                </button>
            @empty
                <div class="px-3 py-4 text-center text-slate-400">
                    No tiene empresas asignadas
                </div>
            @endforelse
        </div>
    </div>
</div>
