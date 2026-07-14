<x-app-layout>
    <div class="px-4 py-2">
        @php
            $userRole = strtolower(Auth::user()->role);
            $isPmo = ($userRole === 'pmo' || $userRole === 'project management officer');

            // Compute total durations and comparisons
            $wbsDurationSum = $project->wbsItems()->sum('estimated_duration_days');
            $startDate = \Carbon\Carbon::parse($project->start_date);
            $endDate = \Carbon\Carbon::parse($project->end_date);
            $projectInitialDuration = $startDate->diffInDays($endDate) + 1;
            $durationDiff = $wbsDurationSum - $projectInitialDuration;

            $draftItemsCount = $project->wbsItems()->where('status', 'draft')->count();
            $stabilityPercentage = $isWbsFinalized ? 100 : ($totalItems > 0 ? round((($totalItems - $draftItemsCount) / $totalItems) * 100) : 0);

            $lastUpdateItem = $project->wbsItems()->orderBy('updated_at', 'desc')->first();
            $lastUpdate = $lastUpdateItem ? $lastUpdateItem->updated_at->diffForHumans() : '-';
        @endphp

        <!-- Top Bar / Header Redesign -->
        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
            <!-- Left: Search input -->
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400 text-xs"></i>
                </span>
                <input type="text" id="wbsSearch" placeholder="Cari modul atau tugas..." 
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

        <!-- Breadcrumbs & Page Info -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">
                    PROYEK: {{ strtoupper($project->title) }} / WBS
                </p>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Work Breakdown Structure</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Rincian struktur kerja untuk memastikan setiap deliverable terdefinisi dengan jelas.
                </p>
            </div>
            <div class="flex items-center gap-2.5 shrink-0 self-end md:self-center">
                @if($isPmo && !$isWbsFinalized)
                    <a href="{{ route('projects.wbs.create', $project->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 hover:text-slate-800 font-bold rounded-xl text-xs transition shadow-sm gap-2">
                        <i class="fas fa-plus text-[10px] text-slate-500"></i>
                        {{ __('Tambah Item') }}
                    </a>
                    @if($totalItems > 0)
                        <form action="{{ route('projects.wbs.finalize', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi WBS ini? Setelah finalized, seluruh item WBS akan dikunci dan tidak dapat diubah atau dihapus.');" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-5 py-2 bg-[#0B1329] hover:bg-[#1A2649] text-white font-bold rounded-xl text-xs transition shadow-md gap-2">
                                {{ __('Finalisasi WBS') }}
                            </button>
                        </form>
                    @endif
                @else
                    @if($isWbsFinalized)
                        <span class="inline-flex items-center gap-1.5 py-2 px-4 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-sm">
                            <i class="fas fa-lock"></i>
                            {{ __('WBS Telah Difinalisasi') }}
                        </span>
                    @endif
                @endif
            </div>
        </div>

        <!-- Navigation Tab Bar -->
        <div class="flex items-center gap-6 border-b border-slate-200 pb-3 mb-6">
            <a href="{{ route('projects.show', $project->id) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                {{ __('Ringkasan') }}
            </a>
            <a href="{{ route('projects.wbs.show', $project->id) }}" class="text-xs font-bold text-blue-600 border-b-2 border-blue-600 pb-3.5 -mb-4 transition">
                {{ __('Work Breakdown Structure') }}
            </a>
            <a href="{{ route('projects.timeline.show', $project->id) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                {{ __('Timeline') }}
            </a>
        </div>

        <!-- Objective reference objective block -->
        @if($project->scope)
            <div class="mb-6 bg-emerald-50/40 border border-emerald-100/60 p-4.5 rounded-2xl">
                <h4 class="text-[9px] font-extrabold text-emerald-800 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                    <i class="fas fa-sitemap text-emerald-600"></i>
                    {{ __('Referensi Tujuan Project Scope') }}
                </h4>
                <p class="text-xs text-slate-700 font-medium leading-relaxed">
                    {{ $project->scope->objective }}
                </p>
            </div>
        @endif

        <!-- Main WBS Table Card -->
        <div class="bg-white border border-slate-200/60 rounded-2xl shadow-sm overflow-hidden mb-6">
            <table class="w-full text-left border-collapse" id="wbsTable">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4 w-28">{{ __('ID TUGAS') }}</th>
                        <th class="px-6 py-4">{{ __('JUDUL TUGAS') }}</th>
                        <th class="px-6 py-4">{{ __('DELIVERABLE') }}</th>
                        <th class="px-6 py-4 w-36">{{ __('DURASI') }}</th>
                        <th class="px-6 py-4 w-32">{{ __('PRIORITAS') }}</th>
                        @if($isPmo && !$isWbsFinalized)
                            <th class="px-6 py-4 w-40 text-right">{{ __('AKSI') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    @if($wbsItems->isEmpty())
                        <tr>
                            <td colspan="{{ ($isPmo && !$isWbsFinalized) ? 6 : 5 }}" class="p-12 text-center text-slate-400">
                                <div class="w-12 h-12 bg-slate-50 border border-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                    <i class="fas fa-sitemap text-lg"></i>
                                </div>
                                <h4 class="font-bold text-sm text-slate-800 mb-1">{{ __('Belum ada item WBS') }}</h4>
                                <p class="text-[11px] text-slate-500 mb-4">{{ __('Struktur kerja WBS belum dibuat untuk proyek ini.') }}</p>
                                @if($isPmo)
                                    <a href="{{ route('projects.wbs.create', $project->id) }}" class="inline-flex items-center px-4 py-2 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold shadow-md transition gap-1.5">
                                        <i class="fas fa-plus text-[10px]"></i>
                                        {{ __('Tambah Item WBS Pertama') }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @else
                        @foreach($wbsItems as $item)
                            @php
                                $level1Index = $loop->iteration;
                                $level1Id = $level1Index . '.0';
                                $p1Colors = [
                                    'low' => 'bg-slate-50 text-slate-500 border-slate-200/50',
                                    'medium' => 'bg-blue-50 text-blue-600 border-blue-100',
                                    'high' => 'bg-rose-50 text-rose-600 border-rose-100',
                                ][$item->priority] ?? 'bg-slate-50 text-slate-500 border-slate-200/50';
                            @endphp
                            <!-- Level 1 Row -->
                            <tr class="hover:bg-slate-50/30 transition-all wbs-row bg-white" data-searchable-item data-search-text="{{ strtolower($item->title . ' ' . $item->description . ' ' . $item->deliverable) }}">
                                <td class="px-6 py-4 font-bold text-[#0B1329] text-sm">{{ $level1Id }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-2.5">
                                        <div class="mt-0.5 text-[#0B1329] shrink-0">
                                            <i class="fa-regular fa-folder-open text-base"></i>
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-800 text-sm leading-snug">{{ $item->title }}</div>
                                            <div class="text-[11px] text-slate-400 font-normal mt-0.5 leading-relaxed">{{ $item->description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="italic text-slate-600 font-medium">{{ $item->deliverable ?: '-' }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-700">
                                    {{ $item->estimated_duration_days ? $item->estimated_duration_days . ' Hari' : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center py-0.5 px-2 rounded-lg text-[9px] font-bold border uppercase tracking-wider {{ $p1Colors }}">
                                        {{ $item->priority === 'high' ? 'TINGGI' : ($item->priority === 'medium' ? 'SEDANG' : 'RENDAH') }}
                                    </span>
                                </td>
                                @if($isPmo && !$isWbsFinalized)
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('projects.wbs.create', ['project' => $project->id, 'parent_id' => $item->id]) }}" 
                                               class="w-7 h-7 inline-flex items-center justify-center bg-blue-50 border border-blue-100 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm"
                                               title="{{ __('Tambah Sub-tugas') }}">
                                                <i class="fas fa-plus text-[10px]"></i>
                                            </a>
                                            <a href="{{ route('projects.wbs.edit', [$project->id, $item->id]) }}" 
                                               class="w-7 h-7 inline-flex items-center justify-center bg-amber-50 border border-amber-200 text-amber-700 rounded-lg hover:bg-amber-600 hover:text-white transition shadow-sm"
                                               title="{{ __('Ubah') }}">
                                                <i class="fas fa-edit text-[10px]"></i>
                                            </a>
                                            @if($item->status === 'draft')
                                                <form action="{{ route('projects.wbs.destroy', [$project->id, $item->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item WBS ini? Menghapus item ini akan ikut menghapus seluruh sub-task di bawahnya.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-7 h-7 inline-flex items-center justify-center bg-rose-50 border border-rose-200 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition shadow-sm" title="{{ __('Hapus') }}">
                                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>

                            <!-- Children level 2 -->
                            @foreach($item->children as $child)
                                @php
                                    $level2Index = $loop->iteration;
                                    $level2Id = $level1Index . '.' . $level2Index;
                                    $p2Colors = [
                                        'low' => 'bg-slate-50 text-slate-500 border-slate-200/50',
                                        'medium' => 'bg-blue-50 text-blue-600 border-blue-100',
                                        'high' => 'bg-rose-50 text-rose-600 border-rose-100',
                                    ][$child->priority] ?? 'bg-slate-50 text-slate-500 border-slate-200/50';
                                @endphp
                                <tr class="hover:bg-slate-50/30 transition-all wbs-row bg-slate-50/10" data-searchable-item data-search-text="{{ strtolower($child->title . ' ' . $child->description . ' ' . $child->deliverable) }}">
                                    <td class="px-6 py-4 font-bold text-slate-600 text-xs pl-8">{{ $level2Id }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-start gap-2 pl-4">
                                            <div class="mt-0.5 text-slate-400 shrink-0">
                                                <i class="fa-solid fa-bars text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-700 text-xs leading-snug">{{ $child->title }}</div>
                                                <div class="text-[10.5px] text-slate-400 font-normal mt-0.5 leading-relaxed">{{ $child->description }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="italic text-slate-500 font-medium">{{ $child->deliverable ?: '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-600 text-xs">
                                        {{ $child->estimated_duration_days ? $child->estimated_duration_days . ' Hari' : '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-lg text-[9px] font-bold border uppercase tracking-wider {{ $p2Colors }}">
                                            {{ $child->priority === 'high' ? 'TINGGI' : ($child->priority === 'medium' ? 'SEDANG' : 'RENDAH') }}
                                        </span>
                                    </td>
                                    @if($isPmo && !$isWbsFinalized)
                                        <td class="px-6 py-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('projects.wbs.create', ['project' => $project->id, 'parent_id' => $child->id]) }}" 
                                                   class="w-7 h-7 inline-flex items-center justify-center bg-blue-50 border border-blue-100 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm"
                                                   title="{{ __('Tambah Sub-tugas') }}">
                                                    <i class="fas fa-plus text-[10px]"></i>
                                                </a>
                                                <a href="{{ route('projects.wbs.edit', [$project->id, $child->id]) }}" 
                                                   class="w-7 h-7 inline-flex items-center justify-center bg-amber-50 border border-amber-200 text-amber-700 rounded-lg hover:bg-amber-600 hover:text-white transition shadow-sm"
                                                   title="{{ __('Ubah') }}">
                                                    <i class="fas fa-edit text-[10px]"></i>
                                                </a>
                                                @if($child->status === 'draft')
                                                    <form action="{{ route('projects.wbs.destroy', [$project->id, $child->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item WBS ini? Menghapus item ini akan ikut menghapus seluruh sub-task di bawahnya.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-7 h-7 inline-flex items-center justify-center bg-rose-50 border border-rose-200 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition shadow-sm" title="{{ __('Hapus') }}">
                                                            <i class="fas fa-trash-alt text-[10px]"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>

                                <!-- Grandchildren level 3 -->
                                @foreach($child->children as $grandchild)
                                    @php
                                        $level3Index = $loop->iteration;
                                        $level3Id = $level1Index . '.' . $level2Index . '.' . $level3Index;
                                        $p3Colors = [
                                            'low' => 'bg-slate-50 text-slate-500 border-slate-200/50',
                                            'medium' => 'bg-blue-50 text-blue-600 border-blue-100',
                                            'high' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        ][$grandchild->priority] ?? 'bg-slate-50 text-slate-500 border-slate-200/50';
                                    @endphp
                                    <tr class="hover:bg-slate-50/30 transition-all wbs-row bg-slate-100/5" data-searchable-item data-search-text="{{ strtolower($grandchild->title . ' ' . $grandchild->description . ' ' . $grandchild->deliverable) }}">
                                        <td class="px-6 py-4 font-bold text-slate-400 text-[11px] pl-10">{{ $level3Id }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-start gap-2 pl-8 relative">
                                                <!-- Bend tree line symbol -->
                                                <span class="absolute left-4 top-4 w-3.5 h-px bg-slate-200"></span>
                                                <div class="mt-0.5 text-slate-400 shrink-0">
                                                    <i class="fa-regular fa-file-lines text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-slate-600 text-xs leading-snug">{{ $grandchild->title }}</div>
                                                    <div class="text-[10px] text-slate-400 font-normal mt-0.5 leading-relaxed">{{ $grandchild->description }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="italic text-slate-400 font-medium">{{ $grandchild->deliverable ?: '-' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 text-[11px]">
                                            {{ $grandchild->estimated_duration_days ? $grandchild->estimated_duration_days . ' Hari' : '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center py-0.5 px-2 rounded-lg text-[9px] font-bold border uppercase tracking-wider {{ $p3Colors }}">
                                                {{ $grandchild->priority === 'high' ? 'TINGGI' : ($grandchild->priority === 'medium' ? 'SEDANG' : 'RENDAH') }}
                                            </span>
                                        </td>
                                        @if($isPmo && !$isWbsFinalized)
                                            <td class="px-6 py-4 text-right">
                                                <div class="inline-flex items-center gap-1.5">
                                                    <a href="{{ route('projects.wbs.edit', [$project->id, $grandchild->id]) }}" 
                                                       class="w-7 h-7 inline-flex items-center justify-center bg-amber-50 border border-amber-200 text-amber-700 rounded-lg hover:bg-amber-600 hover:text-white transition shadow-sm"
                                                       title="{{ __('Ubah') }}">
                                                        <i class="fas fa-edit text-[10px]"></i>
                                                    </a>
                                                    @if($grandchild->status === 'draft')
                                                        <form action="{{ route('projects.wbs.destroy', [$project->id, $grandchild->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item WBS ini?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="w-7 h-7 inline-flex items-center justify-center bg-rose-50 border border-rose-200 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition shadow-sm" title="{{ __('Hapus') }}">
                                                                <i class="fas fa-trash-alt text-[10px]"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Bottom Section: Visualisasi & Ringkasan Metrik -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch mb-8">
            <!-- Left: Visualisasi Struktur -->
            <div class="lg:col-span-2 bg-white border border-slate-200/60 p-6 rounded-2xl shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800 mb-4 flex items-center gap-1.5">
                        <i class="fas fa-chart-network text-blue-500"></i>
                        {{ __('Visualisasi Struktur') }}
                    </h3>
                    
                    <div class="bg-slate-50/50 rounded-xl p-6 border border-slate-100/80 min-h-[260px] flex flex-col items-center justify-center overflow-x-auto">
                        @if($wbsItems->isEmpty())
                            <span class="text-xs text-slate-400 italic">{{ __('Tidak ada item WBS untuk divisualisasikan.') }}</span>
                        @else
                            <div class="flex flex-col gap-10 w-full min-w-[500px]">
                                @foreach($wbsItems->take(3) as $item) <!-- limit to top 3 root elements to prevent overwhelming visual tree -->
                                    @php
                                        $level1Index = $loop->iteration;
                                        $level1Id = $level1Index . '.0';
                                    @endphp
                                    <div class="flex flex-col items-center w-full">
                                        <!-- Root Node (Level 1) -->
                                        <div class="bg-[#0B1329] text-white text-xs font-black py-2.5 px-5 rounded-lg shadow-sm border border-slate-800 z-10 text-center tracking-wide">
                                            {{ $level1Id }} {{ $item->title }}
                                        </div>
                                        
                                        @if($item->children->isNotEmpty())
                                            <!-- Connector line down -->
                                            <div class="w-0.5 h-6 bg-slate-300"></div>
                                            
                                            <!-- Horizontal line container for children -->
                                            <div class="relative w-full flex justify-center">
                                                <!-- Draw horizontal line across the children range -->
                                                @if($item->children->count() > 1)
                                                    <div class="absolute top-0 left-[15%] right-[15%] h-0.5 bg-slate-300"></div>
                                                @endif
                                                
                                                <!-- Children row -->
                                                <div class="flex justify-around w-full pt-6 gap-4">
                                                    @foreach($item->children->take(4) as $child) <!-- Limit to top 4 child elements for cleaner visualization -->
                                                        @php
                                                            $level2Index = $loop->iteration;
                                                            $level2Id = $level1Index . '.' . $level2Index;
                                                        @endphp
                                                        <div class="flex flex-col items-center relative flex-1">
                                                            <!-- Vertical line down to child -->
                                                            <div class="absolute top-[-24px] w-0.5 h-6 bg-slate-300"></div>
                                                            <!-- Child Node -->
                                                            <div class="bg-blue-50 text-blue-800 border border-blue-100 text-[10px] font-bold py-2 px-3 rounded-lg shadow-sm z-10 whitespace-nowrap">
                                                                {{ $level2Id }} {{ $child->title }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @if(!$loop->last)
                                        <div class="border-t border-slate-200/50 my-2"></div>
                                    @endif
                                @endforeach
                                @if($wbsItems->count() > 3)
                                    <div class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">
                                        {{ __('+ Dan ') }}{{ $wbsItems->count() - 3 }}{{ __(' elemen utama lainnya...') }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Metrics Cards -->
            <div class="space-y-6 flex flex-col justify-between">
                <!-- Card 1: Total Durasi -->
                <div class="bg-[#0B1329] p-6 rounded-2xl text-white shadow-md relative overflow-hidden flex-1 flex flex-col justify-between min-h-[140px]">
                    <div class="absolute -right-6 -bottom-6 opacity-10 pointer-events-none text-slate-400">
                        <i class="fas fa-clock text-9xl"></i>
                    </div>
                    
                    <div>
                        <h3 class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('TOTAL DURASI') }}</h3>
                        <div class="text-4xl font-black tracking-tight text-white mt-1">
                            {{ $wbsDurationSum }} Hari
                        </div>
                    </div>
                    <div class="mt-4">
                        @if($durationDiff > 0)
                            <span class="text-xs text-blue-300 font-extrabold flex items-center gap-1.5">
                                <i class="fa-solid fa-arrow-trend-up"></i>
                                +{{ $durationDiff }} Hari dari estimasi awal
                            </span>
                        @elseif($durationDiff < 0)
                            <span class="text-xs text-emerald-400 font-extrabold flex items-center gap-1.5">
                                <i class="fa-solid fa-arrow-trend-down"></i>
                                {{ $durationDiff }} Hari dari estimasi awal
                            </span>
                        @else
                            <span class="text-xs text-slate-300 font-extrabold flex items-center gap-1.5">
                                <i class="fa-solid fa-minus"></i>
                                Sesuai dengan estimasi awal
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Card 2: Kestabilan Struktur -->
                <div class="bg-white border border-slate-200/60 p-6 rounded-2xl shadow-sm flex-1 flex flex-col justify-between min-h-[140px]">
                    <div>
                        <h3 class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">{{ __('KESTABILAN STRUKTUR') }}</h3>
                        <div class="flex items-center gap-4 mt-2">
                            <!-- Visual progress box -->
                            <div class="w-16 h-16 rounded-xl border-2 border-slate-900 bg-slate-50 flex items-center justify-center text-slate-900 font-black text-xl shadow-sm shrink-0">
                                {{ $stabilityPercentage }}%
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800 leading-snug">
                                    @if($isWbsFinalized)
                                        Struktur telah tervalidasi oleh PMO.
                                    @else
                                        Menunggu finalisasi WBS.
                                    @endif
                                </p>
                                <p class="text-[10.5px] text-slate-400 font-semibold mt-1 flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i>
                                    Update terakhir: {{ $lastUpdate }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Frontend Interactive Realtime Filter Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('wbsSearch');
            searchInput?.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.wbs-row');
                
                rows.forEach(row => {
                    if (!query) {
                        row.style.display = '';
                        return;
                    }
                    const text = row.getAttribute('data-search-text') || '';
                    if (text.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</x-app-layout>
