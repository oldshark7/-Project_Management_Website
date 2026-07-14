<div class="h-14 flex items-center px-5 border-b border-slate-100">
    <div class="flex items-center min-w-0" style="padding-left: {{ $depth * 16 }}px">

        @if ($wbs->children->isNotEmpty())
            <i class="fa-regular fa-folder-open text-blue-500 mr-2 text-sm"></i>
        @else
            <i class="fa-regular fa-circle-check text-slate-400 mr-2 text-sm"></i>
        @endif

        <div class="flex flex-col">
            <span class="text-xs font-bold text-slate-700 truncate">
                {{ $wbs->title }}
            </span>

            <span class="text-xs text-slate-400">
                PIC: {{ $wbs->users->pluck('name')->implode(', ') ?: '-' }}
            </span>
        </div>
    </div>
</div>

<!-- loop file to crrate child from parent  -->
@foreach ($wbs->children as $child)
    @include('project-planning.timeline._gantt_row_left', [
        'wbs' => $child,
        'depth' => $depth + 1,
    ])
@endforeach