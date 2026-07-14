<div class="card-background-hr">
    <div
        class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-lg shadow-sm border border-blue-100/50 shrink-0">
        <i class="fas fa-chart-line"></i>
    </div>
    <div class="flex-1 min-w-0">
        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">
            {{ __('BEBAN RATA-RATA') }}
        </span>
        <h3 class="text-3xl font-black text-slate-800 mt-1 tracking-tight">
            {{ $summary['avgWorkload'] }}%
        </h3>
        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden mt-2.5">
            <div class="h-full rounded-full bg-blue-600 transition-all duration-300" style="width: {{ $summary['avgWorkload'] }}%">
            </div>
        </div>
    </div>
</div>
