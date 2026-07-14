@php
    $hasTimeline = $wbs->timelineItem !== null;
@endphp

<div class="flex items-center hover:bg-slate-50/50 transition duration-150 border-b border-slate-100 h-14">
    <!-- Left Cell: Sticky Task Title and Tree Depth Indentation -->
    <div class="w-80 shrink-0 border-r border-slate-100 h-full flex items-center px-5 sticky left-0 bg-white z-20 shadow-[4px_0_8px_-4px_rgba(0,0,0,0.05)]">
        <div class="flex items-center min-w-0" style="padding-left: {{ $depth * 16 }}px">
            <!-- Icon tree/collapse -->
            @if($wbs->children->isNotEmpty())
                <span class="text-slate-400 mr-2 cursor-pointer shrink-0" onclick="window.wbsToggleSection('gantt-children-{{ $wbs->id }}', this)">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </span>
                <i class="fa-regular fa-folder-open text-blue-500 mr-2 shrink-0 text-sm"></i>
            @else
                @if($hasTimeline && $wbs->timelineItem->is_milestone)
                    <i class="fa-solid fa-star text-amber-500 mr-2 shrink-0 text-xs"></i>
                @else
                    <i class="fa-regular fa-circle-check text-slate-400 mr-2 shrink-0 text-sm"></i>
                @endif
            @endif
            
            <div class="truncate">
                <span class="{{ $wbs->children->isNotEmpty() ? 'font-extrabold text-slate-800' : 'font-bold text-slate-700' }} text-xs" title="{{ $wbs->title }}">
                    {{ $wbs->title }}
                </span>
            </div>
        </div>
    </div>

    <!-- Right Cell: Visual Gantt Bar / Grid Area -->
    <div class="flex-1 h-full relative flex items-center min-h-[3.5rem] bg-slate-50/20" 
         style="background-image: repeating-linear-gradient(to right, transparent, transparent 47px, #e2e8f0 47px, #e2e8f0 48px); width: {{ $projectDurationDays * 48 }}px;">
        
        @if($hasTimeline && $projectDurationDays > 0)
            @php
                $startDate = \Carbon\Carbon::parse($wbs->timelineItem->start_date);
                $endDate = \Carbon\Carbon::parse($wbs->timelineItem->end_date);
                
                $leftOffset = $minDate->diffInDays($startDate);
                $duration = $wbs->timelineItem->duration_days;
            @endphp
            
            @if($wbs->timelineItem->is_milestone)
                <!-- Milestone Diamond Marker (centered in the day column) -->
                <div class="absolute flex flex-col items-center justify-center z-10" 
                     style="left: {{ ($leftOffset * 48) + 18 }}px; width: 12px;"
                     title="Milestone: {{ $wbs->timelineItem->milestone_name }} ({{ $startDate->format('d M Y') }})">
                    <div class="w-3.5 h-3.5 bg-amber-500 rotate-45 border border-amber-300 shadow-sm"></div>
                </div>
            @else
                <!-- Regular Gantt Bar -->
                <div class="absolute h-7 rounded-lg bg-blue-600 text-[10px] text-white font-extrabold flex items-center px-3 shadow-md hover:bg-blue-700 transition duration-150 select-none cursor-pointer" 
                     style="left: {{ $leftOffset * 48 }}px; width: {{ $duration * 48 }}px;"
                     title="{{ $wbs->title }} ({{ $startDate->format('d M') }} - {{ $endDate->format('d M') }}, {{ $duration }} Hari)">
                    <span class="truncate max-w-full">
                        {{ $duration }} Hari @if($wbs->timelineItem->status === 'finalized') | Selesai @else | On Progress @endif
                    </span>
                </div>
            @endif
        @else
            <span class="text-[10px] text-slate-400 italic pl-5 font-bold select-none">{{ __('Belum dijadwalkan') }}</span>
        @endif
    </div>
</div>

<!-- Children items -->
@if($wbs->children->isNotEmpty())
    <div id="gantt-children-{{ $wbs->id }}">
        @foreach($wbs->children as $child)
            @include('project-planning.timeline._gantt_row', [
                'wbs' => $child, 
                'depth' => $depth + 1, 
                'projectDurationDays' => $projectDurationDays, 
                'minDate' => $minDate
            ])
        @endforeach
    </div>
@endif
