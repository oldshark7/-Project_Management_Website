<x-app-layout>
    @php
        $user = Auth::user();
        $userRole = strtolower($user->role);
        $isPM = $userRole === 'project manager';
        $isPMO = in_array($userRole, ['pmo', 'project management officer']);
        $isManager = $userRole === 'manager';

        // 1. Proposal
        $proposalDone = ($project->proposal && $project->proposal->status === 'submitted');
        $proposalDraft = ($project->proposal && $project->proposal->status === 'draft');
        
        // 2. Charter
        $charterDone = ($project->charter && $project->charter->status === 'submitted');
        $charterDraft = ($project->charter && $project->charter->status === 'draft');
        
        // 3. Scope
        $scopeDone = ($project->scope && $project->scope->status === 'finalized');
        $scopeDraft = ($project->scope && $project->scope->status === 'draft');
        
        // 4. WBS
        $wbsExists = $project->wbsItems()->exists();
        $wbsDraft = ($wbsExists && \App\Models\WbsItem::where('project_id', $project->id)->where('status', 'draft')->exists());
        $wbsDone = ($wbsExists && !$wbsDraft);
        
        // 5. Timeline
        $timelineExists = $project->timelineItems()->exists();
        $timelineDraft = ($timelineExists && \App\Models\TimelineItem::where('project_id', $project->id)->where('status', 'draft')->exists());
        $timelineDone = ($timelineExists && !$timelineDraft);
        
        // 6. Budget
        $budgetDone = ($project->budgetPlan && $project->budgetPlan->status === 'finalized');
        $budgetDraft = ($project->budgetPlan && $project->budgetPlan->status === 'draft');
        
        // 7. HR Plan
        $hrDone = ($project->humanResourcePlan && $project->humanResourcePlan->status === 'finalized');
        $hrDraft = ($project->humanResourcePlan && $project->humanResourcePlan->status === 'draft');
        
        // 8. Risk Management
        $riskDone = ($project->riskPlan && $project->riskPlan->status === 'finalized');
        $riskDraft = ($project->riskPlan && $project->riskPlan->status === 'draft');

        // Calculate percentage
        $totalSteps = 8;
        $completedCount = 0;
        if ($proposalDone) $completedCount++;
        if ($charterDone) $completedCount++;
        if ($scopeDone) $completedCount++;
        if ($wbsDone) $completedCount++;
        if ($timelineDone) $completedCount++;
        if ($budgetDone) $completedCount++;
        if ($hrDone) $completedCount++;
        if ($riskDone) $completedCount++;
        
        $percent = (int) (($completedCount / $totalSteps) * 100);

        // Determine current active step in planning sequence
        $activeStep = 1;
        if ($project->status === 'draft' || $project->status === 'submitted') {
            $activeStep = 1;
        } elseif (!$proposalDone) {
            $activeStep = 2;
        } elseif (!$charterDone) {
            $activeStep = 3;
        } elseif (!$scopeDone) {
            $activeStep = 4;
        } elseif (!$wbsDone) {
            $activeStep = 5;
        } elseif (!$timelineDone) {
            $activeStep = 6;
        } elseif (!$budgetDone) {
            $activeStep = 7;
        } elseif (!$hrDone) {
            $activeStep = 8;
        } elseif (!$riskDone) {
            $activeStep = 9;
        } else {
            $activeStep = 10;
        }
    @endphp

    <x-slot name="header">
        <div class="flex bg-white border border-slate-100 shadow-sm rounded-2xl p-4 items-center justify-between mb-4">
            <!-- Back & Breadcrumb -->
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.index') }}" class="w-8 h-8 flex items-center justify-center bg-slate-50 hover:bg-slate-100 rounded-lg text-slate-500 hover:text-slate-700 transition-colors">
                    <i class="fas fa-arrow-left text-xs"></i>
                </a>
                <span class="text-sm font-bold text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-500">{{ __('Detail Proyek') }}</span>
            </div>

            <!-- Search Bar (centered/middle) -->
            <div class="relative w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fas fa-search text-slate-400 text-sm"></i>
                </span>
                <input type="text" placeholder="Cari dokumen atau tugas..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-100/60 border border-slate-200/50 rounded-full text-xs text-slate-650 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder-slate-400"
                       readonly>
            </div>

            <!-- Right Icons -->
            <div class="flex items-center gap-5">
                <!-- Notification -->
                <a href="#" class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-regular fa-bell text-lg"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full border border-white"></span>
                </a>

                <!-- User Avatar & Details -->
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-800 leading-tight">{{ $user->name }}</p>
                        <p class="text-[10px] font-semibold text-slate-400 mt-0.5">{{ $user->role }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-full overflow-hidden border border-slate-200 flex items-center justify-center bg-blue-50 text-blue-600 font-bold text-xs shadow-sm">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    
                    <form id="header-logout-form-show" method="POST" action="{{ route('logout') }}" class="hidden">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('header-logout-form-show').submit();" 
                       class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Logout">
                        <i class="fa-solid fa-arrow-right-from-bracket text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pl-4 pt-2 pb-8 pr-2 flex flex-col gap-6">
        <!-- Validation and Session Success Alerts -->
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

        <!-- Row 1: Title and Main Badges -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <!-- Badges -->
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-lg text-[10px] font-bold">
                        ID: PRJ-2026-{{ sprintf("%03d", $project->id) }}
                    </span>
                    @if($project->status === 'draft')
                        <span class="inline-flex items-center px-2.5 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 rounded-full text-[10px] font-bold">
                            {{ __('Draf') }}
                        </span>
                    @elseif($project->status === 'submitted')
                        <span class="inline-flex items-center px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-full text-[10px] font-bold">
                            {{ __('Diajukan') }}
                        </span>
                    @elseif($project->status === 'approved')
                        <span class="inline-flex items-center px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[10px] font-bold">
                            {{ __('Disetujui') }}
                        </span>
                    @elseif($project->status === 'planning')
                        <span class="inline-flex items-center px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[10px] font-bold">
                            {{ __('Berjalan') }}
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
                </div>

                <h1 class="text-3xl font-black text-slate-800 tracking-tight">{{ $project->title }}</h1>
                
                <div class="flex items-center gap-1.5 text-xs text-slate-400 mt-2 font-semibold">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Jakarta Head Office, Indonesia</span>
                </div>
            </div>

            <!-- Header Action buttons -->
            <div class="flex items-center gap-2">
                <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link proyek berhasil disalin!');" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl text-xs transition duration-200 cursor-pointer bg-white">
                    <i class="fa-solid fa-share-nodes"></i>
                    <span>{{ __('Bagikan') }}</span>
                </button>

                @if(($isPM && $project->owner_id === $user->id && in_array($project->status, ['draft', 'rejected'])) || $isManager)
                    <a href="{{ route('projects.edit', $project->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition duration-200 shadow-md shadow-blue-500/10 cursor-pointer">
                        <i class="fa-regular fa-pen-to-square"></i>
                        <span>{{ __('Edit Proyek') }}</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Row 2: Informasi Dasar & Progress Card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informasi Dasar (2/3 width) -->
            <div class="lg:col-span-2 bg-white border border-slate-100 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-blue-600"></i>
                        <span>{{ __('Informasi Dasar') }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 leading-relaxed whitespace-pre-line mb-6">
                        {{ $project->description ?: __('Tidak ada deskripsi detail yang ditambahkan untuk proyek ini.') }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-y-4 gap-x-6 pt-4 border-t border-slate-50 text-xs">
                    <div>
                        <span class="text-slate-400 block font-semibold mb-1">{{ __('Tanggal Mulai') }}</span>
                        <span class="font-bold text-slate-700">
                            {{ $project->start_date ? $project->start_date->format('d F Y') : '-' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-semibold mb-1">{{ __('Estimasi Selesai') }}</span>
                        <span class="font-bold text-slate-700">
                            {{ $project->end_date ? $project->end_date->format('d F Y') : '-' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-semibold mb-1">{{ __('Pemilik Proyek') }}</span>
                        <span class="font-bold text-slate-700">
                            {{ $project->owner ? $project->owner->name : '-' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block font-semibold mb-1">{{ __('Manajer Proyek') }}</span>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-5 h-5 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center font-bold text-blue-600 text-[8px]">
                                {{ $project->manager ? strtoupper(substr($project->manager->name, 0, 2)) : '-' }}
                            </div>
                            <span class="font-bold text-slate-750">
                                {{ $project->manager ? $project->manager->name : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Card (1/3 width) -->
            <div class="bg-gradient-to-br from-[#1964D4] to-[#1E56A0] text-white border border-blue-500/20 rounded-2xl p-6 shadow-sm flex flex-col justify-between min-h-[220px] relative overflow-hidden">
                <div class="absolute right-0 bottom-0 opacity-10 pointer-events-none">
                    <svg class="w-36 h-28 text-white" viewBox="0 0 100 100" fill="currentColor">
                        <line x1="0" y1="80" x2="30" y2="50" stroke="currentColor" stroke-width="5"></line>
                        <line x1="30" y1="50" x2="60" y2="70" stroke="currentColor" stroke-width="5"></line>
                        <line x1="60" y1="70" x2="100" y2="20" stroke="currentColor" stroke-width="5"></line>
                        <polygon points="100,20 85,20 100,35" fill="currentColor"></polygon>
                    </svg>
                </div>

                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-blue-100/90">{{ __('Progress Perencanaan') }}</h2>
                    <div class="flex items-baseline mt-4">
                        <span class="text-5xl font-black tracking-tight">{{ $percent }}%</span>
                    </div>
                    <p class="text-[10px] text-blue-100/80 mt-2 font-medium">
                        {{ $completedCount }} {{ __('dari') }} {{ $totalSteps }} {{ __('tahap perencanaan selesai') }}
                    </p>
                </div>

                <div class="mt-6 z-10">
                    <div class="w-full bg-white/20 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-white h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Stepper Workflow (Alur Kerja Proyek) -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">{{ __('Alur Kerja Proyek') }}</h2>
                </div>
                <span class="text-[10px] font-bold text-slate-400 flex items-center gap-1">
                    <span>{{ __('Geser untuk melihat detail') }}</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </span>
            </div>

            <!-- Stepper Container -->
            <div class="overflow-x-auto pb-4">
                <div class="flex items-center min-w-[700px] justify-between px-4 relative">
                    <!-- Step 1: Request -->
                    <div class="flex flex-col items-center gap-2 z-10 text-center w-24">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-emerald-600 text-white shadow-sm">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <span class="text-[10px] font-bold text-slate-700 mt-1">{{ __('Permintaan') }}</span>
                    </div>

                    <!-- Line 1-2 -->
                    <div class="flex-1 h-1 {{ $proposalDone ? 'bg-emerald-600' : 'bg-slate-200' }} -mx-4"></div>

                    <!-- Step 2: Proposal -->
                    <div class="flex flex-col items-center gap-2 z-10 text-center w-24">
                        @if($proposalDone)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-emerald-600 text-white shadow-sm">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        @elseif($activeStep == 2)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-blue-600 text-white shadow-md ring-4 ring-blue-100">
                                <i class="fa-regular fa-file-lines"></i>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-slate-100 text-slate-400">
                                <i class="fa-regular fa-file-lines"></i>
                            </div>
                        @endif
                        <span class="text-[10px] font-bold {{ $activeStep == 2 ? 'text-blue-600' : 'text-slate-650' }} mt-1">{{ __('Proposal') }}</span>
                    </div>

                    <!-- Line 2-3 -->
                    <div class="flex-1 h-1 {{ $charterDone ? 'bg-emerald-600' : 'bg-slate-200' }} -mx-4"></div>

                    <!-- Step 3: Charter -->
                    <div class="flex flex-col items-center gap-2 z-10 text-center w-24">
                        @if($charterDone)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-emerald-600 text-white shadow-sm">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        @elseif($activeStep == 3)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-blue-600 text-white shadow-md ring-4 ring-blue-100">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-slate-100 text-slate-400">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                        @endif
                        <span class="text-[10px] font-bold {{ $activeStep == 3 ? 'text-blue-600' : 'text-slate-650' }} mt-1">{{ __('Charter') }}</span>
                    </div>

                    <!-- Line 3-4 -->
                    <div class="flex-1 h-1 {{ $scopeDone ? 'bg-emerald-600' : 'bg-slate-200' }} -mx-4"></div>

                    <!-- Step 4: Lingkup -->
                    <div class="flex flex-col items-center gap-2 z-10 text-center w-24">
                        @if($scopeDone)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-emerald-600 text-white shadow-sm">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        @elseif($activeStep == 4)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-blue-600 text-white shadow-md ring-4 ring-blue-100">
                                <i class="fa-solid fa-compass"></i>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-slate-100 text-slate-400">
                                <i class="fa-solid fa-compass"></i>
                            </div>
                        @endif
                        <span class="text-[10px] font-bold {{ $activeStep == 4 ? 'text-blue-600' : 'text-slate-650' }} mt-1">{{ __('Lingkup') }}</span>
                    </div>

                    <!-- Line 4-5 -->
                    <div class="flex-1 h-1 {{ $wbsDone ? 'bg-emerald-600' : 'bg-slate-200' }} -mx-4"></div>

                    <!-- Step 5: WBS -->
                    <div class="flex flex-col items-center gap-2 z-10 text-center w-24">
                        @if($wbsDone)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-emerald-600 text-white shadow-sm">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        @elseif($activeStep == 5)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-blue-600 text-white shadow-md ring-4 ring-blue-100">
                                <i class="fa-solid fa-sitemap"></i>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-slate-100 text-slate-400">
                                <i class="fa-solid fa-sitemap"></i>
                            </div>
                        @endif
                        <span class="text-[10px] font-bold {{ $activeStep == 5 ? 'text-blue-600' : 'text-slate-650' }} mt-1">{{ __('WBS') }}</span>
                    </div>

                    <!-- Line 5-6 -->
                    <div class="flex-1 h-1 {{ $timelineDone ? 'bg-emerald-600' : 'bg-slate-200' }} -mx-4"></div>

                    <!-- Step 6: Timeline -->
                    <div class="flex flex-col items-center gap-2 z-10 text-center w-24">
                        @if($timelineDone)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-emerald-600 text-white shadow-sm">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        @elseif($activeStep == 6)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-blue-600 text-white shadow-md ring-4 ring-blue-100">
                                <i class="fa-regular fa-calendar-days"></i>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-slate-100 text-slate-400">
                                <i class="fa-regular fa-calendar-days"></i>
                            </div>
                        @endif
                        <span class="text-[10px] font-bold {{ $activeStep == 6 ? 'text-blue-600' : 'text-slate-650' }} mt-1">{{ __('Timeline') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 4: Dokumen & Persetujuan Cards Grid -->
        <div>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6 flex items-center gap-2">
                <i class="fa-solid fa-folder-closed text-blue-600"></i>
                <span>{{ __('Dokumen & Persetujuan') }}</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Card 1: Project Request (Always Selesai) -->
                <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative hover:scale-[1.01] transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fa-regular fa-file"></i>
                            </span>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[9px] font-bold">
                                {{ __('Selesai') }}
                            </span>
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">{{ __('Project Request') }}</h3>
                        <p class="text-[10px] text-slate-450 mt-1">{{ __('Penanggung Jawab: Project Manager') }}</p>
                    </div>
                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] text-slate-400 mt-4">
                        <span>{{ __('Dibuat pada: ') }}{{ $project->created_at->format('d/m/y') }}</span>
                    </div>
                </div>

                <!-- Card 2: Project Proposal -->
                <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative hover:scale-[1.01] transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <i class="fa-regular fa-file-lines"></i>
                            </span>
                            @if($proposalDone)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[9px] font-bold">
                                    {{ __('Selesai') }}
                                </span>
                            @elseif($proposalDraft)
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-full text-[9px] font-bold">
                                    {{ __('Draft') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-50 text-slate-400 border border-slate-200 rounded-full text-[9px] font-bold">
                                    {{ __('Terkunci') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">{{ __('Proposal Bisnis') }}</h3>
                        <p class="text-[10px] text-slate-450 mt-1">{{ __('Penanggung Jawab: PM & Manager') }}</p>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] mt-4">
                        @if($project->proposal)
                            <span class="text-slate-400">{{ __('Update: ') }}{{ $project->proposal->updated_at->format('d/m/y') }}</span>
                            <a href="{{ route('projects.proposal.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                {{ __('Lihat') }}
                            </a>
                        @else
                            <span class="text-slate-350 italic">{{ __('Belum dibuat') }}</span>
                            @if($isManager && $project->status === 'approved')
                                <a href="{{ route('projects.proposal.create', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Buat') }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Card 3: Project Charter -->
                <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative hover:scale-[1.01] transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <i class="fa-solid fa-file-signature"></i>
                            </span>
                            @if($charterDone)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[9px] font-bold">
                                    {{ __('Selesai') }}
                                </span>
                            @elseif($charterDraft)
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-full text-[9px] font-bold">
                                    {{ __('Draft') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-50 text-slate-400 border border-slate-200 rounded-full text-[9px] font-bold">
                                    {{ __('Terkunci') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">{{ __('Project Charter') }}</h3>
                        <p class="text-[10px] text-slate-450 mt-1">{{ __('Penanggung Jawab: Manager') }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] mt-4">
                        @if($project->charter)
                            <span class="text-slate-400">{{ __('Update: ') }}{{ $project->charter->updated_at->format('d/m/y') }}</span>
                            <a href="{{ route('projects.charter.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                {{ __('Lihat') }}
                            </a>
                        @else
                            <span class="text-slate-350 italic">{{ __('Belum dibuat') }}</span>
                            @if($isManager && $project->status === 'approved')
                                <a href="{{ route('projects.charter.create', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Buat') }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Card 4: Pernyataan Lingkup (Project Scope) -->
                @php
                    $scopeCreated = (bool)$project->scope;
                    $canCreateScope = !$scopeCreated && $isManager && $project->status === 'planning';
                    $canEditScope = $scopeCreated && $scopeDraft && $isManager;
                @endphp
                <div class="rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative hover:scale-[1.01] transition-all {{ ($scopeCreated && $scopeDraft && $isManager) ? 'bg-blue-50 border-2 border-blue-400' : 'bg-white border border-slate-100' }}">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-compass"></i>
                            </span>
                            @if($scopeDone)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[9px] font-bold">
                                    {{ __('Selesai') }}
                                </span>
                            @elseif($scopeDraft)
                                <span class="px-2 py-0.5 bg-blue-600 text-white rounded-full text-[9px] font-bold">
                                    {{ __('Draft') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-50 text-slate-400 border border-slate-200 rounded-full text-[9px] font-bold">
                                    {{ __('Terkunci') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">{{ __('Pernyataan Lingkup') }}</h3>
                        <p class="text-[10px] text-slate-450 mt-1">{{ __('Penanggung Jawab: Manager') }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] mt-4">
                        @if($scopeCreated)
                            @if($canEditScope)
                                <div class="flex items-center gap-2 w-full justify-between">
                                    <a href="{{ route('projects.scope.edit', $project->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 text-center transition">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('projects.scope.finalize', $project->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 rounded-lg font-bold transition cursor-pointer">
                                            {{ __('Finalisasi') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-slate-400">{{ __('Update: ') }}{{ $project->scope->updated_at->format('d/m/y') }}</span>
                                <a href="{{ route('projects.scope.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Lihat') }}
                                </a>
                            @endif
                        @else
                            <span class="text-slate-350 italic">{{ __('Belum dibuat') }}</span>
                            @if($canCreateScope)
                                <a href="{{ route('projects.scope.create', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Buat') }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Card 5: Work Breakdown Structure (WBS) -->
                @php
                    $canCreateWbs = !$wbsExists && $isPMO && $project->status === 'planning' && $scopeDone;
                    $canEditWbs = $wbsExists && $wbsDraft && $isPMO;
                @endphp
                <div class="rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative hover:scale-[1.01] transition-all {{ ($wbsExists && $wbsDraft && $isPMO) ? 'bg-blue-50 border-2 border-blue-400' : 'bg-white border border-slate-100' }}">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                                <i class="fa-solid fa-sitemap"></i>
                            </span>
                            @if($wbsDone)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[9px] font-bold">
                                    {{ __('Selesai') }}
                                </span>
                            @elseif($wbsExists)
                                <span class="px-2 py-0.5 bg-blue-600 text-white rounded-full text-[9px] font-bold">
                                    {{ __('Draft') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-55 text-slate-400 border border-slate-150 rounded-full text-[9px] font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[8px]"></i> {{ __('Terkunci') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">{{ __('Work Breakdown Structure') }}</h3>
                        <p class="text-[10px] text-slate-450 mt-1">{{ __('Penanggung Jawab: PMO') }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] mt-4">
                        @if($wbsExists)
                            @if($canEditWbs)
                                <div class="flex items-center gap-2 w-full justify-between">
                                    <a href="{{ route('projects.wbs.show', $project->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 text-center transition">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('projects.wbs.finalize', $project->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 rounded-lg font-bold transition cursor-pointer">
                                            {{ __('Finalisasi') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-slate-400">{{ __('WBS Terkonfigurasi') }}</span>
                                <a href="{{ route('projects.wbs.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Lihat') }}
                                </a>
                            @endif
                        @else
                            <span class="text-slate-350 italic text-[9px]">{{ $scopeDone ? __('Belum dibuat') : __('Butuh Scope Final') }}</span>
                            @if($canCreateWbs)
                                <a href="{{ route('projects.wbs.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Buat') }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Card 6: Jadwal Proyek (Timeline / Gantt) -->
                @php
                    $canCreateTimeline = !$timelineExists && $isPMO && $project->status === 'planning' && $wbsDone;
                    $canEditTimeline = $timelineExists && $timelineDraft && $isPMO;
                @endphp
                <div class="rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative hover:scale-[1.01] transition-all {{ ($timelineExists && $timelineDraft && $isPMO) ? 'bg-blue-50 border-2 border-blue-400' : 'bg-white border border-slate-100' }}">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center">
                                <i class="fa-regular fa-calendar-days"></i>
                            </span>
                            @if($timelineDone)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[9px] font-bold">
                                    {{ __('Selesai') }}
                                </span>
                            @elseif($timelineExists)
                                <span class="px-2 py-0.5 bg-blue-600 text-white rounded-full text-[9px] font-bold">
                                    {{ __('Draft') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-55 text-slate-400 border border-slate-150 rounded-full text-[9px] font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[8px]"></i> {{ __('Terkunci') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">{{ __('Jadwal & Gantt Chart') }}</h3>
                        <p class="text-[10px] text-slate-450 mt-1">{{ __('Penanggung Jawab: PMO') }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] mt-4">
                        @if($timelineExists)
                            @if($canEditTimeline)
                                <div class="flex items-center gap-2 w-full justify-between">
                                    <a href="{{ route('projects.timeline.show', $project->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 text-center transition">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('projects.timeline.finalize', $project->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 rounded-lg font-bold transition cursor-pointer">
                                            {{ __('Finalisasi') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-slate-400">{{ __('Timeline Terjadwal') }}</span>
                                <a href="{{ route('projects.timeline.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Lihat') }}
                                </a>
                            @endif
                        @else
                            <span class="text-slate-350 italic text-[9px]">{{ $wbsDone ? __('Belum dibuat') : __('Butuh WBS Final') }}</span>
                            @if($canCreateTimeline)
                                <a href="{{ route('projects.timeline.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Buat') }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Card 7: Rencana Anggaran (Budget Plan) -->
                @php
                    $budgetCreated = (bool)$project->budgetPlan;
                    $canCreateBudget = !$budgetCreated && $isManager && $project->status === 'planning' && $timelineDone;
                    $canEditBudget = $budgetCreated && $budgetDraft && $isManager;
                    
                    // PM has no access to budget details at all (403 block context)
                    $hideBudgetInfo = $isPM;
                @endphp
                @if(!$hideBudgetInfo)
                <div class="rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative hover:scale-[1.01] transition-all {{ ($budgetCreated && $budgetDraft && $isManager) ? 'bg-blue-50 border-2 border-blue-400' : 'bg-white border border-slate-100' }}">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center">
                                <i class="fa-solid fa-wallet"></i>
                            </span>
                            @if($budgetDone)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[9px] font-bold">
                                    {{ __('Selesai') }}
                                </span>
                            @elseif($budgetCreated)
                                <span class="px-2 py-0.5 bg-blue-600 text-white rounded-full text-[9px] font-bold">
                                    {{ __('Draft') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-55 text-slate-400 border border-slate-150 rounded-full text-[9px] font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[8px]"></i> {{ __('Terkunci') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">{{ __('Rencana Anggaran (RAB)') }}</h3>
                        <p class="text-[10px] text-slate-450 mt-1">{{ __('Penanggung Jawab: Manager') }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] mt-4">
                        @if($budgetCreated)
                            @if($canEditBudget)
                                <div class="flex items-center gap-2 w-full justify-between">
                                    <a href="{{ route('projects.budget.edit', $project->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 text-center transition">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('projects.budget.finalize', $project->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 rounded-lg font-bold transition cursor-pointer">
                                            {{ __('Finalisasi') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-slate-450 font-semibold">{{ __('Total: Rp ') }}{{ number_format($project->budgetPlan->total_budget, 0, ',', '.') }}</span>
                                <a href="{{ route('projects.budget.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Lihat') }}
                                </a>
                            @endif
                        @else
                            <span class="text-slate-350 italic text-[9px]">{{ $timelineDone ? __('Belum dibuat') : __('Butuh Timeline Final') }}</span>
                            @if($canCreateBudget)
                                <a href="{{ route('projects.budget.create', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Buat') }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
                @endif

                <!-- Card 8: Human Resource Plan -->
                @php
                    $hrCreated = (bool)$project->humanResourcePlan;
                    $canCreateHr = !$hrCreated && $isPMO && $project->status === 'planning' && $budgetDone;
                    $canEditHr = $hrCreated && $hrDraft && $isPMO;
                    
                    $hideHrInfo = $isPM;
                @endphp
                @if(!$hideHrInfo)
                <div class="rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative hover:scale-[1.01] transition-all {{ ($hrCreated && $hrDraft && $isPMO) ? 'bg-blue-50 border-2 border-blue-400' : 'bg-white border border-slate-100' }}">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                                <i class="fa-solid fa-users-gear"></i>
                            </span>
                            @if($hrDone)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[9px] font-bold">
                                    {{ __('Selesai') }}
                                </span>
                            @elseif($hrCreated)
                                <span class="px-2 py-0.5 bg-blue-600 text-white rounded-full text-[9px] font-bold">
                                    {{ __('Draft') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-55 text-slate-400 border border-slate-150 rounded-full text-[9px] font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[8px]"></i> {{ __('Terkunci') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">{{ __('Alokasi Sumber Daya (HR)') }}</h3>
                        <p class="text-[10px] text-slate-450 mt-1">{{ __('Penanggung Jawab: PMO') }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] mt-4">
                        @if($hrCreated)
                            @if($canEditHr)
                                <div class="flex items-center gap-2 w-full justify-between">
                                    <a href="{{ route('projects.human-resource.edit', $project->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 text-center transition">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('projects.human-resource.finalize', $project->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 rounded-lg font-bold transition cursor-pointer">
                                            {{ __('Finalisasi') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-slate-400">{{ __('SDM Dialokasikan') }}</span>
                                <a href="{{ route('projects.human-resource.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Lihat') }}
                                </a>
                            @endif
                        @else
                            <span class="text-slate-350 italic text-[9px]">{{ $budgetDone ? __('Belum dibuat') : __('Butuh Anggaran Final') }}</span>
                            @if($canCreateHr)
                                <a href="{{ route('projects.human-resource.create', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Buat') }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
                @endif

                <!-- Card 9: Risk Management -->
                @php
                    $riskCreated = (bool)$project->riskPlan;
                    $canCreateRisk = !$riskCreated && $isPMO && $project->status === 'planning' && $hrDone;
                    $canEditRisk = $riskCreated && $riskDraft && $isPMO;
                    
                    $hideRiskInfo = $isPM;
                @endphp
                @if(!$hideRiskInfo)
                <div class="rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative hover:scale-[1.01] transition-all {{ ($riskCreated && $riskDraft && $isPMO) ? 'bg-blue-50 border-2 border-blue-400' : 'bg-white border border-slate-100' }}">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-red-50 text-red-650 flex items-center justify-center">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </span>
                            @if($riskDone)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-[9px] font-bold">
                                    {{ __('Selesai') }}
                                </span>
                            @elseif($riskCreated)
                                <span class="px-2 py-0.5 bg-blue-600 text-white rounded-full text-[9px] font-bold">
                                    {{ __('Draft') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-55 text-slate-400 border border-slate-150 rounded-full text-[9px] font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[8px]"></i> {{ __('Terkunci') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">{{ __('Manajemen Risiko') }}</h3>
                        <p class="text-[10px] text-slate-450 mt-1">{{ __('Penanggung Jawab: PMO') }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] mt-4">
                        @if($riskCreated)
                            @if($canEditRisk)
                                <div class="flex items-center gap-2 w-full justify-between">
                                    <a href="{{ route('projects.risk-management.edit', $project->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 text-center transition">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('projects.risk-management.finalize', $project->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 rounded-lg font-bold transition cursor-pointer">
                                            {{ __('Finalisasi') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-slate-400">{{ __('Risiko Terpetakan') }}</span>
                                <a href="{{ route('projects.risk-management.show', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Lihat') }}
                                </a>
                            @endif
                        @else
                            <span class="text-slate-350 italic text-[9px]">{{ $hrDone ? __('Belum dibuat') : __('Butuh HR Plan Final') }}</span>
                            @if($canCreateRisk)
                                <a href="{{ route('projects.risk-management.create', $project->id) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ __('Buat') }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>