<tr class="hover:bg-slate-50/30 transition">

    <td class="px-6 py-4">
        <div class="flex items-center min-w-0" style="padding-left: {{ $depth * 24 }}px">
            @if ($depth > 0)
                <span class="text-slate-300 mr-2.5 font-mono shrink-0 select-none">↳</span>
            @endif
            <div class="truncate">
                <span class="font-extrabold text-slate-800 text-xs md:text-sm block" title="{{ $wbs->title }}">{{ $wbs->title }}</span>
                <span class="flex flex-col text-[9.5px] text-slate-400 block font-bold uppercase mt-0.5 tracking-wider">ID TUGAS: #{{ $wbs->id }}</span>

                <span class="text-xs text-slate-400">
                    PIC: {{ $wbs->users->pluck('name')->implode(', ') ?: '-' }}
                </span>
            </div>
        </div>
    </td>

    <td class="px-6 py-4">
        @if ($wbs->timelineItem)
            <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                <i class="fa-regular fa-calendar-days text-slate-400"></i>
                {{ $wbs->timelineItem->start_date->format('d M Y') }} s/d
                {{ $wbs->timelineItem->end_date->format('d M Y') }}
            </div>
            @if ($wbs->timelineItem->dependencyWbsItem)
                <div
                    class="text-[9px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200/60 inline-flex items-center mt-1 font-extrabold uppercase tracking-wide">
                    <i class="fas fa-link mr-1"></i>
                    Predecessor: #{{ $wbs->timelineItem->dependency_wbs_item_id }}
                    ({{ Str::limit($wbs->timelineItem->dependencyWbsItem->title, 20) }})
                </div>
            @endif
        @else
            <span
                class="text-[9px] text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-lg border border-rose-200/50 inline-flex items-center font-extrabold uppercase tracking-wide">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ __('Belum dijadwalkan') }}
            </span>
        @endif
    </td>

    <td class="px-6 py-4 font-black text-slate-600 text-xs">
        @if ($wbs->timelineItem)
            {{ $wbs->timelineItem->duration_days }} {{ __('Hari') }}
        @else
            -
        @endif
    </td>

    <td class="px-6 py-4">
        @if ($wbs->timelineItem && $wbs->timelineItem->is_milestone)
            <span
                class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wide bg-indigo-50 text-indigo-700 border border-indigo-200">
                <i class="fas fa-flag text-[9px]"></i>
                {{ $wbs->timelineItem->milestone_name }}
            </span>
        @else
            <span class="text-slate-400 italic text-xs font-semibold">-</span>
        @endif
    </td>

    <td class="px-6 py-4 text-right">
        @php
            $isAssigned = $wbs->isFullyAssigned();
        @endphp
        @if (!$isAssigned)
            <button onclick="openAssignModal({{ $wbs->id }})"
                class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg">
                Assign
            </button>
        @else
            <span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-lg font-bold">
                Finalized
            </span>
        @endif
    </td>
</tr>

@foreach ($wbs->children as $child)
    @include('project-planning.timeline._timeline_row_hr', ['wbs' => $child, 'depth' => $depth + 1])
@endforeach
