@php
    $hasTimeline = $wbs->timelineItem !== null;
@endphp

<div class="h-14 flex items-center border-b border-slate-100 relative bg-slate-50/20" style="background-image: repeating-linear-gradient(to right, transparent, transparent 47px, #e2e8f0 47px, #e2e8f0 48px);">

    @if($hasTimeline && $projectDurationDays > 0)

        @php
            $startDate = \Carbon\Carbon::parse($wbs->timelineItem->start_date);
            $endDate = \Carbon\Carbon::parse($wbs->timelineItem->end_date);

            $leftOffset = $minDate->diffInDays($startDate);
            $duration = $wbs->timelineItem->duration_days;
        @endphp

        @if($wbs->timelineItem->is_milestone)
            <div class="absolute"
                 style="left: {{ ($leftOffset * 48) + 18 }}px;">
                <div class="w-3.5 h-3.5 bg-amber-500 rotate-45"></div>
            </div>
        @else
            <div class="absolute h-7 bg-blue-600 rounded-lg text-white text-[10px] flex items-center px-3"
                 style="left: {{ $leftOffset * 48 }}px; width: {{ $duration * 48 }}px;">
                {{ $duration }} Hari
            </div>
        @endif

    @endif

</div>

{{-- CHILD --}}
@foreach($wbs->children as $child)
    @include('project-planning.timeline._gantt_row_right', [
        'wbs' => $child,
        'depth' => $depth + 1,
        'projectDurationDays' => $projectDurationDays,
        'minDate' => $minDate
    ])
@endforeach