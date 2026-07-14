<x-app-layout>
    @php
        $user = Auth::user();
        $isPM = strtolower($user->role) === 'project manager';
        $isPMO = in_array(strtolower($user->role), ['pmo', 'project management officer']);
        $isManager = strtolower($user->role) === 'manager';

        // Base query for stats calculation
        $statsQuery = \App\Models\Project::query();
        if ($isPM) {
            $statsQuery->where('owner_id', $user->id);
        }

        $totalProjectsCount = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->where('status', 'submitted')->count();
        $approvedCount = (clone $statsQuery)->whereIn('status', ['approved', 'planning', 'completed'])->count();
        $rejectedCount = (clone $statsQuery)->where('status', 'rejected')->count();
    @endphp

    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    <div class="pl-4 pt-2 pb-8 pr-2 flex flex-col gap-6">
        <!-- Row 1: Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Card 1: Total Proyek -->
            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm border-l-4 border-l-blue-600 hover:scale-[1.01] transition-all duration-300 flex flex-col justify-between min-h-[100px]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Proyek') }}</span>
                <div class="flex items-baseline justify-between mt-2">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-black text-slate-800 tracking-tight">{{ $totalProjectsCount }}</span>
                        <span class="text-[10px] font-semibold text-emerald-500 flex items-center gap-0.5 ml-2">
                            <i class="fa-solid fa-arrow-trend-up"></i> 12%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Menunggu Persetujuan -->
            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm border-l-4 border-l-amber-500 hover:scale-[1.01] transition-all duration-300 flex flex-col justify-between min-h-[100px]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Menunggu Persetujuan') }}</span>
                <div class="flex items-baseline justify-between mt-2">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-black text-slate-800 tracking-tight">{{ sprintf("%02d", $pendingCount) }}</span>
                        <span class="text-[10px] font-bold text-amber-500 ml-2">
                            {{ __('Aktif') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Disetujui -->
            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm border-l-4 border-l-emerald-500 hover:scale-[1.01] transition-all duration-300 flex flex-col justify-between min-h-[100px]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Disetujui') }}</span>
                <div class="flex items-baseline justify-between mt-2">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-black text-slate-800 tracking-tight">{{ $approvedCount }}</span>
                        <span class="text-[10px] font-semibold text-emerald-500 flex items-center gap-1 ml-2">
                            <i class="fa-regular fa-circle-check"></i> Terverifikasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Ditolak -->
            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm border-l-4 border-l-rose-500 hover:scale-[1.01] transition-all duration-300 flex flex-col justify-between min-h-[100px]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Ditolak') }}</span>
                <div class="flex items-baseline justify-between mt-2">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-black text-slate-800 tracking-tight">{{ sprintf("%02d", $rejectedCount) }}</span>
                        <span class="text-[10px] font-bold text-rose-500 ml-2">
                            {{ __('Perlu Review') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Filter Tabs -->
        <div class="flex flex-wrap items-center gap-2">
            <button onclick="filterProjects('all', this)" class="filter-btn bg-blue-600 text-white rounded-full px-5 py-1.5 text-xs font-bold shadow-md shadow-blue-500/10 transition-all cursor-pointer">
                {{ __('Semua') }}
            </button>
            <button onclick="filterProjects('draft', this)" class="filter-btn bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-full px-5 py-1.5 text-xs font-bold transition-all cursor-pointer">
                {{ __('Draf') }}
            </button>
            <button onclick="filterProjects('submitted', this)" class="filter-btn bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-full px-5 py-1.5 text-xs font-bold transition-all cursor-pointer">
                {{ __('Diajukan') }}
            </button>
            <button onclick="filterProjects('approved', this)" class="filter-btn bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-full px-5 py-1.5 text-xs font-bold transition-all cursor-pointer">
                {{ __('Disetujui') }}
            </button>
            <button onclick="filterProjects('rejected', this)" class="filter-btn bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-full px-5 py-1.5 text-xs font-bold transition-all cursor-pointer">
                {{ __('Ditolak') }}
            </button>
            <button onclick="filterProjects('planning', this)" class="filter-btn bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-full px-5 py-1.5 text-xs font-bold transition-all cursor-pointer">
                {{ __('Perencanaan') }}
            </button>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex flex-col gap-1 shadow-sm">
                @foreach($errors->all() as $error)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-rose-500"></i>
                        <span>{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Row 3: Projects Table Card -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            @if($projects->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 text-slate-350 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-folder-open text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-base text-slate-800 mb-1">{{ __('Tidak ada proyek ditemukan') }}</h4>
                    <p class="text-xs text-slate-400">{{ __('Belum ada proyek yang dapat ditampilkan untuk peran Anda saat ini.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                <th class="px-6 py-4 w-1/3">{{ __('Judul Proyek') }}</th>
                                <th class="px-6 py-4 w-1/6">{{ __('Pemilik') }}</th>
                                <th class="px-6 py-4 w-1/6">{{ __('Manajer') }}</th>
                                <th class="px-6 py-4 w-1/5">{{ __('Rentang Tanggal') }}</th>
                                <th class="px-6 py-4 w-1/12">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-center w-1/6">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            @foreach($projects as $project)
                                @php
                                    // Custom visual division based on project metadata or owner role
                                    $division = 'General Operations';
                                    if ($project->owner) {
                                        $ownerRole = strtolower($project->owner->role);
                                        if (strpos($ownerRole, 'manager') !== false && strpos($ownerRole, 'project') === false) {
                                            $division = 'Finance & Management';
                                        } elseif (strpos($ownerRole, 'project manager') !== false) {
                                            $division = 'Divisi IT Support';
                                        } elseif (strpos($ownerRole, 'pmo') !== false || strpos($ownerRole, 'officer') !== false) {
                                            $division = 'Project Management Office';
                                        }
                                    }
                                @endphp
                                <tr class="project-row hover:bg-slate-50/30 transition-colors" data-status="{{ $project->status }}">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-850 project-title">{{ $project->title }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5 line-clamp-1 project-desc">{{ $project->description ?: __('Tidak ada deskripsi.') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-700">{{ $project->owner ? $project->owner->name : '-' }}</div>
                                        <div class="text-[9px] text-slate-400 mt-0.5">{{ $division }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-medium">
                                        {{ $project->manager ? $project->manager->name : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 font-medium">
                                        @if($project->start_date && $project->end_date)
                                            {{ $project->start_date->format('d M Y') }} - {{ $project->end_date->format('d M Y') }}
                                        @elseif($project->start_date)
                                            {{ __('Mulai: ') . $project->start_date->format('d M Y') }}
                                        @else
                                            <span class="text-slate-350 italic">{{ __('Belum diatur') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($project->status === 'draft')
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-slate-100 text-slate-750 border border-slate-200 rounded-full text-[10px] font-bold">
                                                {{ __('Draf') }}
                                            </span>
                                        @elseif($project->status === 'submitted')
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-blue-50 text-blue-705 border border-blue-100 rounded-full text-[10px] font-bold">
                                                {{ __('Diajukan') }}
                                            </span>
                                        @elseif($project->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-emerald-50 text-emerald-705 border border-emerald-100 rounded-full text-[10px] font-bold">
                                                {{ __('Disetujui') }}
                                            </span>
                                        @elseif($project->status === 'planning')
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-indigo-50 text-indigo-705 border border-indigo-100 rounded-full text-[10px] font-bold">
                                                {{ __('Perencanaan') }}
                                            </span>
                                        @elseif($project->status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-rose-50 text-rose-705 border border-rose-100 rounded-full text-[10px] font-bold">
                                                {{ __('Ditolak') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-slate-100 text-slate-750 border border-slate-200 rounded-full text-[10px] font-bold">
                                                {{ ucfirst($project->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-3">
                                            <!-- View Button (Always visible) -->
                                            <a href="{{ route('projects.show', $project->id) }}" class="text-blue-500 hover:text-blue-700 transition-colors p-1" title="View Detail">
                                                <i class="fa-regular fa-eye text-base"></i>
                                            </a>

                                            @if($isManager && $project->status === 'submitted')
                                                <!-- Action forms inline for Manager review -->
                                                <form action="{{ route('projects.update', $project->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-750 text-white font-bold rounded-lg text-[9px] uppercase tracking-wide cursor-pointer transition shadow-sm">
                                                        {{ __('Setujui') }}
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('projects.update', $project->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="px-2.5 py-1 border border-rose-500 hover:bg-rose-50 text-rose-600 font-bold rounded-lg text-[9px] uppercase tracking-wide cursor-pointer transition">
                                                        {{ __('Tolak') }}
                                                    </button>
                                                </form>
                                            @else
                                                <!-- Edit Button for PM or Manager -->
                                                @if(($isPM && $project->owner_id === $user->id && in_array($project->status, ['draft', 'rejected'])) || $isManager)
                                                    <a href="{{ route('projects.edit', $project->id) }}" class="text-indigo-500 hover:text-indigo-700 transition-colors p-1" title="Edit Proyek">
                                                        <i class="fa-regular fa-pen-to-square text-base"></i>
                                                    </a>
                                                @endif

                                                <!-- Delete Button for PM (Draft only) -->
                                                @if($isPM && $project->owner_id === $user->id && $project->status === 'draft')
                                                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-rose-500 hover:text-rose-700 transition-colors p-1 cursor-pointer" title="Hapus Proyek">
                                                            <i class="fa-regular fa-trash-can text-base"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Pagination -->
                <div class="flex items-center justify-between p-5 border-t border-slate-100 bg-white">
                    <span class="text-xs text-slate-400 font-bold" id="showing-count-text">
                        {{ __('Menampilkan 1-') }}{{ $projects->count() }}{{ __(' dari ') }}{{ $projects->count() }}{{ __(' proyek') }}
                    </span>
                    <!-- Visual page indicator matching mockup -->
                    <div class="flex items-center gap-1">
                        <button class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-650 transition-colors" disabled>
                            <i class="fas fa-chevron-left text-[10px]"></i>
                        </button>
                        <button class="w-8 h-8 rounded-lg bg-blue-600 text-white font-bold text-xs shadow-sm flex items-center justify-center">
                            1
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-650 transition-colors" disabled>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Client-Side Search and Filter Logic -->
    <script>
        function searchProjects() {
            const query = document.getElementById('project-search-input').value.toLowerCase();
            document.querySelectorAll('.project-row').forEach(row => {
                const title = row.querySelector('.project-title').textContent.toLowerCase();
                const desc = row.querySelector('.project-desc').textContent.toLowerCase();
                if (title.includes(query) || desc.includes(query)) {
                    row.classList.remove('hidden-by-search');
                } else {
                    row.classList.add('hidden-by-search');
                }
                updateRowVisibility(row);
            });
            updateShowingCount();
        }
        
        function filterProjects(status, btn) {
            // Remove active class from all filter buttons
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-500/10');
                b.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
            });
            
            // Add active class to current button
            btn.classList.add('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-500/10');
            btn.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');

            document.querySelectorAll('.project-row').forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.classList.remove('hidden-by-filter');
                } else {
                    row.classList.add('hidden-by-filter');
                }
                updateRowVisibility(row);
            });
            updateShowingCount();
        }
        
        function updateRowVisibility(row) {
            if (row.classList.contains('hidden-by-search') || row.classList.contains('hidden-by-filter')) {
                row.classList.add('hidden');
            } else {
                row.classList.remove('hidden');
            }
        }
        
        function updateShowingCount() {
            const visibleRows = document.querySelectorAll('.project-row:not(.hidden)').length;
            const totalRows = document.querySelectorAll('.project-row').length;
            const countText = document.getElementById('showing-count-text');
            if (countText) {
                countText.textContent = `Menampilkan 1-${visibleRows} dari ${totalRows} proyek`;
            }
        }
    </script>
</x-app-layout>
