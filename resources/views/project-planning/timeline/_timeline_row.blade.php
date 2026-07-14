<tr class="hover:bg-slate-50/30 transition">
    <td class="px-6 py-4">
        <div class="flex items-center min-w-0" style="padding-left: {{ $depth * 24 }}px">
            @if($depth > 0)
                <span class="text-slate-300 mr-2.5 font-mono shrink-0 select-none">↳</span>
            @endif
            <div class="truncate">
                <span class="font-extrabold text-slate-800 text-xs md:text-sm block" title="{{ $wbs->title }}">{{ $wbs->title }}</span>
                <span class="text-[9.5px] text-slate-400 block font-bold uppercase mt-0.5 tracking-wider">ID TUGAS: #{{ $wbs->id }}</span>
            </div>
        </div>
    </td>
    <td class="px-6 py-4">
        @if($wbs->timelineItem)
            <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                <i class="fa-regular fa-calendar-days text-slate-400"></i>
                {{ $wbs->timelineItem->start_date->format('d M Y') }} s/d {{ $wbs->timelineItem->end_date->format('d M Y') }}
            </div>
            @if($wbs->timelineItem->dependencyWbsItem)
                <div class="text-[9px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200/60 inline-flex items-center mt-1 font-extrabold uppercase tracking-wide">
                    <i class="fas fa-link mr-1"></i>
                    Predecessor: #{{ $wbs->timelineItem->dependency_wbs_item_id }} ({{ Str::limit($wbs->timelineItem->dependencyWbsItem->title, 20) }})
                </div>
            @endif
        @else
            <span class="text-[9px] text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-lg border border-rose-200/50 inline-flex items-center font-extrabold uppercase tracking-wide">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ __('Belum dijadwalkan') }}
            </span>
        @endif
    </td>
    <td class="px-6 py-4 font-black text-slate-600 text-xs">
        @if($wbs->timelineItem)
            {{ $wbs->timelineItem->duration_days }} {{ __('Hari') }}
        @else
            -
        @endif
    </td>
    <td class="px-6 py-4">
        @if($wbs->timelineItem && $wbs->timelineItem->is_milestone)
            <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wide bg-indigo-50 text-indigo-700 border border-indigo-200">
                <i class="fas fa-flag text-[9px]"></i>
                {{ $wbs->timelineItem->milestone_name }}
            </span>
        @else
            <span class="text-slate-400 italic text-xs font-semibold">-</span>
        @endif
    </td>
    <td class="px-6 py-4 text-right">
        <div class="inline-flex gap-2">
            @if((strtolower(Auth::user()->role) === 'pmo' || strtolower(Auth::user()->role) === 'project management officer') && !$isTimelineFinalized)
                @if($wbs->timelineItem)
                    <a href="{{ route('projects.timeline.edit', [$project->id, $wbs->timelineItem->id]) }}" 
                       class="inline-flex items-center justify-center w-7 h-7 text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-600 hover:text-white transition shadow-sm" 
                       title="{{ __('Ubah Jadwal') }}">
                        <i class="fas fa-edit text-xs"></i>
                    </a>
                    
                    @if($wbs->timelineItem->status === 'draft')
                        <form action="{{ route('projects.timeline.destroy', [$project->id, $wbs->timelineItem->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal timeline untuk item ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center justify-center w-7 h-7 text-rose-600 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-600 hover:text-white transition shadow-sm" 
                                    title="{{ __('Hapus Jadwal') }}">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('projects.timeline.create', $project->id) }}?wbs_id={{ $wbs->id }}" 
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-[9px] font-extrabold uppercase tracking-wide text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm">
                        <i class="fas fa-calendar-plus text-[10px]"></i>
                        {{ __('Jadwalkan') }}
                    </a>
                @endif
            @else
                <span class="text-[10px] text-slate-400 italic font-semibold">{{ __('Kunci') }}</span>
            @endif
        </div>
    </td>
</tr>

@foreach($wbs->children as $child)
    @include('project-planning.timeline._timeline_row', ['wbs' => $child, 'depth' => $depth + 1])
@endforeach
