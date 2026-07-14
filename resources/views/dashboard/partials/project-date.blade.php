<div class="flex gap-10">
    <!-- start date -->
    <div class="flex gap-3 items-center">
        <i class="fas fa-calendar text-slate-300 text-3xl"></i>
        <div>
            <h2 class="text-sm text-slate-400">Start Date</h2>
            <p class="text-md font-semibold">{{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</p>
        </div>
    </div>

    <!-- line separate -->
    <div>
        <div class="h-full bg-slate-200 w-[1.5px]">
        </div>
    </div>
    
    <!-- deadline -->
    <div class="flex gap-3 items-center">
        <i class="fas fa-calendar text-slate-300 text-3xl"></i>
        <div>
            <h2 class="text-sm text-slate-400">Deadline</h2>
            <p class="text-md font-semibold">{{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}</p>
        </div>
    </div>

    <!-- line separate -->
    <div>
        <div class="h-full bg-slate-200 w-[1.5px]">
        </div>
    </div>

    <!-- sisa waktu -->
    <div class="flex gap-3 items-center">
        <i class="fas fa-clock text-slate-300 text-3xl"></i>
        <div>
            <h2 class="text-sm text-slate-400">Sisa Waktu</h2>
            <p class="text-md font-semibold">
                @if($remainingDays === null)
                    -
                @elseif($remainingDays < 0)
                    Terlambat {{ abs($remainingDays) }} hari
                @elseif($remainingDays == 0)
                    Deadline hari ini
                @else
                    {{ $remainingDays }} hari lagi
                @endif
            </p>
        </div>
    </div>
</div>