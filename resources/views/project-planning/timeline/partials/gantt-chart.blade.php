<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    {{-- Title --}}
    <div class="mb-4 flex justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-slate-800">
                {{ __('Gantt Chart Proyek') }}
            </h3>
            <p class="text-xs text-slate-400 mt-1">
                {{ __('Visualisasi timeline pekerjaan berdasarkan WBS') }}
            </p>
        </div>

        <div class="bg-slate-100 p-1 rounded-xl flex items-center shadow-sm border border-slate-200/40">
            <button type="button" id="tab-table-btn" onclick="setView('timeline')"
                class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 bg-white text-slate-800 shadow-sm flex items-center gap-1.5">
                <i class="fas fa-table text-[11px]"></i>
                Mingguan
            </button>
            <button type="button" id="tab-gantt-btn" onclick="setView('gantt')"
                class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-800 flex items-center gap-1.5">
                <i class="fas fa-chart-gantt text-[11px]"></i>
                Bulanan
            </button>
        </div>
    </div>

    <div id="gantt-view">
        @if ($projectDurationDays > 0)
            <div class="border border-slate-100 rounded-xl overflow-hidden">

                <!-- header / task title section -->
                <div class="flex border-b border-slate-100 bg-white">
                    <!-- header table -->
                    <div class="w-80 shrink-0 border-r px-5 py-3">
                        <span class="text-xs font-extrabold text-slate-600 uppercase">
                            Task / WBS
                        </span>
                    </div>

                    <!-- ganttchart content -->
                    <div class="flex-1 overflow-x-auto no-scrollbar" id="gantt-header">
                        <div class="flex" style="min-width: {{ $projectDurationDays * 48 }}px">
                            @for ($i = 0; $i < $projectDurationDays; $i++)
                                @php
                                    $currentDate = \Carbon\Carbon::parse($minDate)->copy()->addDays($i);
                                @endphp
                                <div class="w-12 shrink-0 text-center border-r py-2">
                                    <div class="text-[10px] font-bold text-slate-700">
                                        {{ $currentDate->format('d') }}
                                    </div>
                                    <div class="text-[9px] text-slate-400">
                                        {{ $currentDate->format('M') }}
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="flex">

                    {{-- LEFT COLUMN (FIXED) --}}
                    <div class="w-80 shrink-0 border-r">
                        @foreach ($wbsItems as $wbs)
                            @include('project-planning.timeline._gantt_row_left', [
                                'wbs' => $wbs,
                                'depth' => 0,
                            ])
                        @endforeach
                    </div>
                    
                    {{-- RIGHT COLUMN (ONE SCROLL) --}}
                    <div class="flex-1 overflow-x-auto" id="gantt-body">
                        <div style="min-width: {{ $projectDurationDays * 48 }}px">
                            @foreach ($wbsItems as $wbs)
                                @include('project-planning.timeline._gantt_row_right', [
                                    'wbs' => $wbs,
                                    'depth' => 0,
                                    'projectDurationDays' => $projectDurationDays,
                                    'minDate' => $minDate,
                                ])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center text-slate-400 py-10">
                Belum ada timeline
            </div>
        @endif
    </div>

    <div id="timeline-view" class="hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold">
                    <tr>
                        <th class="px-6 py-3 text-left">Task</th>
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Durasi</th>
                        <th class="px-6 py-3 text-left">Milestone</th>
                        <th class="px-9 py-3 text-right">Assign</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($wbsItems as $wbs)
                        @include('project-planning.timeline._timeline_row_hr', [
                            'wbs' => $wbs,
                            'depth' => 0,
                            'isTimelineFinalized' => true,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setView('timeline');
    });

    function setView(type) {
        const gantt = document.getElementById('gantt-view');
        const timeline = document.getElementById('timeline-view');

        const tabTableBtn = document.getElementById('tab-table-btn');
        const tabGanttBtn = document.getElementById('tab-gantt-btn');

        if (type === 'gantt') {
            gantt.classList.remove('hidden');
            timeline.classList.add('hidden');

            tabGanttBtn.className = "px-4 py-1.5 text-xs font-bold rounded-lg bg-white text-slate-800 shadow-sm flex items-center gap-1.5";
            tabTableBtn.className = "px-4 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1.5";

        } else {
            gantt.classList.add('hidden');
            timeline.classList.remove('hidden');

            tabTableBtn.className = "px-4 py-1.5 text-xs font-bold rounded-lg bg-white text-slate-800 shadow-sm flex items-center gap-1.5";
            tabGanttBtn.className = "px-4 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1.5";
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const header = document.getElementById('gantt-header');
        const body = document.getElementById('gantt-body');

        body.addEventListener('scroll', function () {
            header.scrollLeft = body.scrollLeft;
        });

        header.addEventListener('scroll', function () {
            body.scrollLeft = header.scrollLeft;
        });
    });

    let isSyncing = false;

    body.addEventListener('scroll', function () {
        if (!isSyncing) {
            isSyncing = true;
            header.scrollLeft = body.scrollLeft;
            isSyncing = false;
        }
    });

    header.addEventListener('scroll', function () {
        if (!isSyncing) {
            isSyncing = true;
            body.scrollLeft = header.scrollLeft;
            isSyncing = false;
        }
    });
</script>
