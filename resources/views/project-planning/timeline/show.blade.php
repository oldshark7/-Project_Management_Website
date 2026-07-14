<x-app-layout>
    <div class="px-4 py-2">
        @php
            $userRole = strtolower(Auth::user()->role);
            $isPmo = ($userRole === 'pmo' || $userRole === 'project management officer');
            $isDraft = !$isTimelineFinalized;

            // Predecessor conflict checking logic
            $conflictCount = 0;
            foreach ($timelineItems as $item) {
                if ($item->dependency_wbs_item_id) {
                    $depTimeline = $timelineItems->where('wbs_item_id', $item->dependency_wbs_item_id)->first();
                    if ($depTimeline) {
                        $depEndDate = \Carbon\Carbon::parse($depTimeline->end_date);
                        $itemStartDate = \Carbon\Carbon::parse($item->start_date);
                        if ($itemStartDate->lt($depEndDate)) {
                            $conflictCount++;
                        }
                    }
                }
            }

            // Team allocation info
            $allocatedTeamMembers = collect();
            if ($project->humanResourcePlan) {
                $memberIds = $project->humanResourcePlan->humanResourceItems()->pluck('team_member_id')->filter()->unique();
                $allocatedTeamMembers = \App\Models\TeamMember::whereIn('id', $memberIds)->get();
            }
        @endphp

        <!-- Top Bar / Header Redesign -->
        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
            <!-- Left: Search input -->
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400 text-xs"></i>
                </span>
                <input type="text" id="timelineSearch" placeholder="Cari jadwal atau tugas..." 
                       class="w-full pl-9 pr-4 py-1.5 bg-slate-100/60 border border-slate-200/50 rounded-full text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder-slate-400">
            </div>

            <!-- Right: Actions & User Info -->
            <div class="flex items-center gap-5 justify-end shrink-0 w-full sm:w-auto">
                <!-- Notification Bell -->
                <a href="#" class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-regular fa-bell text-lg"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full border border-white"></span>
                </a>

                <!-- Profile Info -->
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                    <div class="text-right hidden md:block">
                        <p class="text-xs font-bold text-slate-800 leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] font-semibold text-slate-400 mt-0.5">{{ Auth::user()->role }}</p>
                    </div>
                    <!-- Avatar circle -->
                    <div class="w-9 h-9 rounded-full overflow-hidden border border-slate-200 flex items-center justify-center bg-blue-50 text-blue-600 font-bold text-xs shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fas fa-exclamation-circle text-rose-500 text-sm"></i>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Banner Alur Kerja Proyek (Status Bar) -->
        <div class="mb-6">
            @if($isTimelineFinalized)
                <div class="p-4.5 bg-blue-50 border border-blue-100 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shadow-sm">
                            <i class="fas fa-check-circle text-sm"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-blue-900 block leading-snug">Siap untuk Perencanaan Anggaran</span>
                            <span class="text-[11px] text-blue-600 block mt-0.5">Jadwal pelaksanaan proyek telah dikunci dan siap untuk proses input Anggaran Belanja (RAB).</span>
                        </div>
                    </div>
                    @if(in_array(strtolower(Auth::user()->role), ['manager', 'pmo', 'project management officer']))
                        <a href="{{ route('projects.budget.show', $project->id) }}" class="px-4 py-2 bg-[#0B1329] hover:bg-[#1A2649] text-white text-xs font-bold rounded-xl shadow-md transition whitespace-nowrap">
                            Lanjut ke Anggaran
                        </a>
                    @endif
                </div>
            @else
                @if($wbsItemsCount > $timelineItemsCount)
                    <div class="p-4.5 bg-amber-50 border border-amber-100 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shadow-sm">
                                <i class="fas fa-exclamation-triangle text-sm"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-amber-900 block leading-snug">Jadwal Belum Lengkap</span>
                                <span class="text-[11px] text-amber-600 block mt-0.5">Ada {{ $wbsItemsCount - $timelineItemsCount }} tugas WBS yang belum dijadwalkan. Selesaikan penjadwalan sebelum melakukan finalisasi.</span>
                            </div>
                        </div>
                        @if($isPmo)
                            <a href="{{ route('projects.timeline.create', $project->id) }}" class="px-4 py-2 bg-[#0B1329] hover:bg-[#1A2649] text-white text-xs font-bold rounded-xl shadow-md transition whitespace-nowrap">
                                Jadwalkan Tugas
                            </a>
                        @endif
                    </div>
                @else
                    <div class="p-4.5 bg-blue-50 border border-blue-100 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shadow-sm">
                                <i class="fas fa-info-circle text-sm"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-blue-900 block leading-snug">Seluruh Tugas Telah Dijadwalkan</span>
                                <span class="text-[11px] text-blue-600 block mt-0.5">Silakan lakukan finalisasi agar modul budget planning dapat terbuka secara otomatis.</span>
                            </div>
                        </div>
                        @if($isPmo)
                            <form action="{{ route('projects.timeline.finalize', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi timeline ini? Setelah finalized, seluruh jadwal timeline akan dikunci dan tidak dapat diubah atau dihapus.');">
                                @csrf
                                <button type="submit" class="px-5 py-2 bg-[#0B1329] hover:bg-[#1A2649] text-white text-xs font-bold rounded-xl shadow-md transition whitespace-nowrap">
                                    Finalisasi Timeline
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <!-- Breadcrumbs & Action Toolbar -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">
                    PROYEK: {{ strtoupper($project->title) }} / TIMELINE
                </p>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Timeline & Gantt Chart</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Kelola jadwal pengerjaan proyek dan kelola ketergantungan antar tugas.
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3.5 self-end md:self-center shrink-0">
                <!-- Segmented Controls / View Mode -->
                <div class="bg-slate-100 p-1 rounded-xl flex items-center shadow-sm border border-slate-200/40">
                    <button type="button" id="tab-table-btn" class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 bg-white text-slate-800 shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-table text-[11px]"></i>
                        Mingguan
                    </button>
                    <button type="button" id="tab-gantt-btn" class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-800 flex items-center gap-1.5">
                        <i class="fas fa-chart-gantt text-[11px]"></i>
                        Bulanan
                    </button>
                </div>

                <a href="{{ route('project-planning.timeline.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 hover:text-slate-800 font-bold rounded-xl text-xs transition shadow-sm gap-2">
                    <i class="fas fa-filter text-[10px] text-slate-400"></i>
                    {{ __('Filter') }}
                </a>

                @if($isPmo && $isDraft && $wbsItemsCount > $timelineItemsCount)
                    <a href="{{ route('projects.timeline.create', $project->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-[#0B1329] hover:bg-[#1E293B] text-white font-bold rounded-xl text-xs transition shadow-md gap-2">
                        <i class="fas fa-plus text-[10px]"></i>
                        {{ __('Tugas Baru') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Navigation Tab Bar -->
        <div class="flex items-center gap-6 border-b border-slate-200 pb-3 mb-6">
            <a href="{{ route('projects.show', $project->id) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                {{ __('Ringkasan') }}
            </a>
            <a href="{{ route('projects.wbs.show', $project->id) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                {{ __('Work Breakdown Structure') }}
            </a>
            <a href="{{ route('projects.timeline.show', $project->id) }}" class="text-xs font-bold text-blue-600 border-b-2 border-blue-600 pb-3.5 -mb-4 transition">
                {{ __('Timeline') }}
            </a>
        </div>

        <!-- Tab 1: Table List View (styled as weekly details table) -->
        <div id="tab-table-content" class="block mb-8">
            <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
                @if($timelineItems->isEmpty())
                    <div class="p-12 text-center text-slate-400">
                        <div class="w-16 h-16 bg-slate-50 border border-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <i class="fas fa-calendar-alt text-2xl"></i>
                        </div>
                        <h4 class="font-extrabold text-lg text-slate-800 mb-1">{{ __('Belum ada tugas dijadwalkan') }}</h4>
                        <p class="text-xs text-slate-500 mb-4">{{ __('Jadwal pelaksanaan kerja (timeline) belum dibuat.') }}</p>
                        @if($isPmo && $isDraft)
                            <a href="{{ route('projects.timeline.create', $project->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition gap-2">
                                <i class="fas fa-plus text-[10px]"></i>
                                {{ __('Jadwalkan Tugas Pertama') }}
                            </a>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="timelineTable">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="px-6 py-4">{{ __('Item WBS') }}</th>
                                    <th class="px-6 py-4">{{ __('Jadwal Pelaksanaan') }}</th>
                                    <th class="px-6 py-4 w-40">{{ __('Durasi') }}</th>
                                    <th class="px-6 py-4 w-44">{{ __('Milestone') }}</th>
                                    <th class="px-6 py-4 w-36 text-right">{{ __('Aksi') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                                @foreach($wbsItems as $wbs)
                                    @include('project-planning.timeline._timeline_row', ['wbs' => $wbs, 'depth' => 0, 'project' => $project, 'isTimelineFinalized' => $isTimelineFinalized])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tab 2: Gantt Chart View -->
        <div id="tab-gantt-content" class="hidden mb-8">
            @if($timelineItems->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-12 text-center text-slate-400">
                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i class="fas fa-chart-gantt text-2xl"></i>
                    </div>
                    <h4 class="font-extrabold text-lg text-slate-800 mb-1">{{ __('Gantt Chart Kosong') }}</h4>
                    <p class="text-xs text-slate-500">{{ __('Jadwalkan minimal satu tugas untuk memvisualisasikan Gantt Chart.') }}</p>
                </div>
            @else
                @php
                    $dates = [];
                    $months = [];
                    if ($minDate && $maxDate) {
                        $tempDate = $minDate->copy();
                        while ($tempDate->lte($maxDate)) {
                            $dates[] = $tempDate->copy();
                            $monthKey = $tempDate->format('M Y');
                            if (!isset($months[$monthKey])) {
                                $months[$monthKey] = 0;
                            }
                            $months[$monthKey]++;
                            $tempDate->addDay();
                        }
                    }
                    $totalColumns = count($dates);
                @endphp

                <!-- Gantt Scroll Container -->
                <div class="border border-slate-200/60 rounded-2xl overflow-hidden bg-white shadow-sm flex flex-col mb-4">
                    <div class="overflow-x-auto relative">
                        <div style="min-width: {{ 320 + ($totalColumns * 48) }}px" class="flex flex-col">
                            <!-- Gantt Header -->
                            <div class="flex bg-slate-50/80 border-b border-slate-100 items-stretch sticky top-0 z-30">
                                <!-- Left Header Column -->
                                <div class="w-80 shrink-0 border-r border-slate-100 flex items-center px-5 font-extrabold text-[9px] text-slate-400 uppercase tracking-wider sticky left-0 bg-slate-50 z-40">
                                    {{ __('Tugas & Struktur WBS') }}
                                </div>
                                <!-- Right Header Calendar Columns -->
                                <div class="flex-1 flex flex-col min-w-0">
                                    <!-- Month/Year Header Row -->
                                    <div class="flex border-b border-slate-200/50 bg-slate-50/50 items-center">
                                        @foreach($months as $monthName => $daysCount)
                                            <div class="text-[9px] font-extrabold text-slate-400 border-r border-slate-100/50 text-center uppercase tracking-wider py-1.5 shrink-0" 
                                                 style="width: {{ $daysCount * 48 }}px">
                                                {{ $monthName }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <!-- Day Number Header Row -->
                                    <div class="flex bg-white items-center">
                                        @foreach($dates as $date)
                                            <div class="text-[10px] font-extrabold text-slate-500 border-r border-slate-100/30 text-center w-12 shrink-0 py-2.5 bg-white">
                                                {{ $date->format('d') }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Gantt Rows -->
                            <div class="divide-y divide-slate-100">
                                @foreach($wbsItems as $wbs)
                                    @include('project-planning.timeline._gantt_row', [
                                        'wbs' => $wbs, 
                                        'depth' => 0, 
                                        'projectDurationDays' => $totalColumns, 
                                        'minDate' => $minDate
                                    ])
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Legend / Helpers -->
                <div class="flex flex-wrap items-center justify-start gap-6 px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wide py-2">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>{{ __('Klik & Seret untuk mengubah durasi') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-left-right text-xs text-slate-400"></i>
                        <span>{{ __('Scroll horizontal untuk linimasa penuh') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 bg-amber-500 rotate-45 border border-amber-300 shadow-sm shrink-0"></div>
                        <span>{{ __('Penanda Milestone') }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Panel Bawah Kumpulan Kartu Metrik -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch mb-8">
            <!-- Card 1: Total Durasi -->
            <div class="bg-white border border-slate-200/60 p-5 rounded-2xl shadow-sm flex flex-col justify-between min-h-[110px]">
                <h3 class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('TOTAL DURASI') }}</h3>
                <div class="text-2xl font-black text-slate-800 mt-2">
                    {{ $timelineItems->sum('duration_days') }} Hari Kerja
                </div>
                <div class="text-[10px] text-slate-400 font-semibold mt-1">Total durasi seluruh jadwal kerja.</div>
            </div>

            <!-- Card 2: Tugas Selesai / Terjadwal -->
            <div class="bg-white border border-slate-200/60 p-5 rounded-2xl shadow-sm flex flex-col justify-between min-h-[110px]">
                <h3 class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('TUGAS TERJADWAL') }}</h3>
                <div class="text-2xl font-black text-slate-800 mt-2">
                    {{ $timelineItemsCount }} / {{ $wbsItemsCount }} Total
                </div>
                <div class="text-[10px] text-slate-400 font-semibold mt-1">Jumlah tugas WBS yang terjadwal.</div>
            </div>

            <!-- Card 3: Critical Path / Konflik -->
            <div class="bg-white border border-slate-200/60 p-5 rounded-2xl shadow-sm flex flex-col justify-between min-h-[110px]">
                <h3 class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('CRITICAL PATH') }}</h3>
                <div class="flex items-center gap-2 mt-2">
                    @if($conflictCount > 0)
                        <span class="text-rose-600 font-black text-xl flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                            {{ $conflictCount }} Konflik
                        </span>
                    @else
                        <span class="text-emerald-600 font-black text-xl flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            Jadwal Valid
                        </span>
                    @endif
                </div>
                <div class="text-[10px] text-slate-400 font-semibold mt-1">Ketergantungan jadwal predecessor.</div>
            </div>

            <!-- Card 4: Alokasi Tim -->
            <div class="bg-white border border-slate-200/60 p-5 rounded-2xl shadow-sm flex flex-col justify-between min-h-[110px]">
                <h3 class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">{{ __('ALOKASI TIM') }}</h3>
                <div class="flex items-center -space-x-2 overflow-hidden mt-2 shrink-0">
                    @forelse($allocatedTeamMembers->take(4) as $member)
                        <div class="inline-block h-7 w-7 rounded-full ring-2 ring-white bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center text-[10px] font-extrabold shadow-sm" title="{{ $member->name }} ({{ $member->role_name }})">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                    @empty
                        <!-- Mockup avatars if empty to keep it beautiful -->
                        <div class="inline-block h-7 w-7 rounded-full ring-2 ring-white bg-slate-100 text-slate-600 flex items-center justify-center text-[10px] font-bold shadow-sm" title="Project Owner">
                            {{ strtoupper(substr($project->owner->name ?? 'PM', 0, 2)) }}
                        </div>
                        <div class="inline-block h-7 w-7 rounded-full ring-2 ring-white bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-bold shadow-sm" title="JD (Developer)">
                            JD
                        </div>
                        <div class="inline-block h-7 w-7 rounded-full ring-2 ring-white bg-purple-50 text-purple-600 flex items-center justify-center text-[10px] font-bold shadow-sm" title="AN (Designer)">
                            AN
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 pl-3">+2</span>
                    @endforelse
                    @if($allocatedTeamMembers->count() > 4)
                        <span class="text-[10px] font-bold text-slate-400 pl-3">+{{ $allocatedTeamMembers->count() - 4 }}</span>
                    @endif
                </div>
                <div class="text-[10px] text-slate-400 font-semibold mt-1">Sumber daya manusia terlibat.</div>
            </div>
        </div>
    </div>

    <!-- Active Tab switching Vanilla Script & Realtime Search -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabTableBtn = document.getElementById('tab-table-btn');
            const tabGanttBtn = document.getElementById('tab-gantt-btn');
            const tabTableContent = document.getElementById('tab-table-content');
            const tabGanttContent = document.getElementById('tab-gantt-content');

            if (tabTableBtn && tabGanttBtn && tabTableContent && tabGanttContent) {
                tabTableBtn.addEventListener('click', function () {
                    // Update buttons styling
                    tabTableBtn.className = "px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 bg-white text-slate-800 shadow-sm flex items-center gap-1.5";
                    tabGanttBtn.className = "px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-800 flex items-center gap-1.5";

                    // Toggle contents
                    tabTableContent.classList.remove('hidden');
                    tabTableContent.classList.add('block');
                    tabGanttContent.classList.remove('block');
                    tabGanttContent.classList.add('hidden');
                });

                tabGanttBtn.addEventListener('click', function () {
                    // Update buttons styling
                    tabGanttBtn.className = "px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 bg-white text-slate-800 shadow-sm flex items-center gap-1.5";
                    tabTableBtn.className = "px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-800 flex items-center gap-1.5";

                    // Toggle contents
                    tabGanttContent.classList.remove('hidden');
                    tabGanttContent.classList.add('block');
                    tabTableContent.classList.remove('block');
                    tabTableContent.classList.add('hidden');
                });
            }

            // Real-time table search filter
            const searchInput = document.getElementById('timelineSearch');
            searchInput?.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#timelineTable tbody tr');
                
                rows.forEach(row => {
                    if (!query) {
                        row.style.display = '';
                        return;
                    }
                    const text = row.textContent.toLowerCase();
                    if (text.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Collapse/Expand Section function for Gantt Chart Tree
            window.wbsToggleSection = function(sectionId, element) {
                const target = document.getElementById(sectionId);
                if (target) {
                    const isHidden = target.style.display === 'none';
                    target.style.display = isHidden ? '' : 'none';
                    
                    // Toggle chevron icon
                    const icon = element.querySelector('i');
                    if (icon) {
                        if (isHidden) {
                            icon.className = 'fas fa-chevron-down text-[10px]';
                        } else {
                            icon.className = 'fas fa-chevron-right text-[10px]';
                        }
                    }
                }
            };
        });
    </script>
</x-app-layout>
