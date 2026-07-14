<!-- Card 3: Status Kapasitas Proyek -->
<div class="bg-[#0B1329] text-white rounded-2xl p-5 shadow-sm flex flex-col justify-between relative overflow-hidden">
    <!-- Background graph icon watermark -->
    <div class="absolute right-3 bottom-1 text-[#1E293B]/60 text-6xl pointer-events-none font-bold">
        <i class="fas fa-chart-bar opacity-30"></i>
    </div>
    <div class="relative z-10">
        <h4 class="text-xs font-bold text-white tracking-wide uppercase">
            {{ __('Status Kapasitas Proyek') }}</h4>
        <p class="text-[10px] font-medium text-slate-300 mt-1 leading-snug">
            {{ __('Tim saat ini berada dalam ambang batas optimal (60% - 85%).') }}
        </p>
    </div>
    <div class="flex gap-1.5 mt-4 relative z-10">
        <span class="px-2 py-1 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[9px] font-extrabold">
            {{ $summary['overloadCount'] }} Overload
        </span>
        <span
            class="px-2 py-1 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[9px] font-extrabold">
            {{ $summary['optimalCount'] }} Optimal
        </span>
        <span class="px-2 py-1 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[9px] font-extrabold">
            {{ $summary['underloadCount'] }} Underload
        </span>
    </div>
</div>
