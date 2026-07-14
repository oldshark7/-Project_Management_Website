<x-app-layout>
    <div class="px-4 py-2">
        <!-- Top Bar / Header Redesign -->
        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
            <!-- Left: Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('projects.show', $project->id) }}" class="text-slate-400 hover:text-slate-600 transition font-medium">Inisiasi Proyek</a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('projects.show', $project->id) }}" class="text-slate-400 hover:text-slate-600 transition font-semibold">Proyek #{{ $project->id }}</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-bold">Detail Piagam Proyek</span>
            </div>

            <!-- Right: Actions & User Info -->
            <div class="flex items-center gap-4 w-full sm:w-auto justify-end">
                @if($charter)
                    <!-- Status Badge -->
                    @php
                        $statusClasses = [
                            'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
                            'submitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'reviewed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'revision_needed' => 'bg-rose-50 text-rose-700 border-rose-200',
                        ][$charter->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    @endphp
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border {{ $statusClasses }}">
                        Status: {{ $charter->status }}
                    </span>

                    <a href="{{ route('projects.charter.download', $project->id) }}" 
                       class="px-4 py-2 bg-rose-600 hover:bg-rose-750 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-file-pdf"></i> {{ __('Download PDF') }}
                    </a>

                    @if(strtolower(Auth::user()->role) === 'manager' && $project->status === 'approved' && $charter->status === 'draft')
                        <div class="flex items-center gap-2">
                            <a href="{{ route('projects.charter.edit', $project->id) }}" 
                               class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                <i class="fas fa-edit text-slate-500"></i> {{ __('Ubah') }}
                            </a>
                            <form action="{{ route('projects.charter.update', $project->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi piagam proyek ini? Setelah difinalisasi, Anda tidak dapat mengedit lagi.');">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="action" value="submit">
                                <button type="submit" class="px-4 py-2 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold transition shadow-md">
                                    {{ __('Finalisasi') }}
                                </button>
                            </form>
                        </div>
                    @endif
                @endif

                <!-- Divider -->
                <div class="hidden sm:block border-l border-slate-200 h-8"></div>

                <!-- Profile Info -->
                <div class="flex items-center gap-2.5">
                    <div class="text-right hidden md:block">
                        <p class="text-[10px] font-semibold text-slate-400 leading-none">Pengguna Aktif</p>
                        <p class="text-xs font-bold text-slate-800 mt-1 leading-none">{{ Auth::user()->name }}</p>
                    </div>
                    <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 flex items-center justify-center bg-blue-50 text-blue-600 font-bold text-[11px] shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
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

        @if(session('info'))
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                <span class="font-semibold">{{ session('info') }}</span>
            </div>
        @endif

        @if(!$charter)
            <!-- Empty State Redesign -->
            <div class="bg-white p-12 rounded-2xl border border-slate-100 shadow-sm text-center max-w-xl mx-auto my-12">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 border border-blue-100 shadow-sm">
                    <i class="fa-solid fa-file-signature text-2xl"></i>
                </div>
                <h4 class="font-extrabold text-lg text-slate-800 mb-2">{{ __('Piagam Proyek Belum Dibuat') }}</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mb-8 leading-relaxed">
                    {{ __('Project Charter mendefinisikan tujuan proyek, kasus bisnis, batasan, milestone, anggaran, dan pemangku kepentingan kunci.') }}
                </p>

                @if(strtolower(Auth::user()->role) === 'manager' && $project->status === 'approved')
                    <a href="{{ route('projects.charter.create', $project->id) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#0B1329] hover:bg-[#1E293B] text-white font-bold rounded-xl text-xs transition shadow-md gap-2">
                        <i class="fas fa-plus text-[10px]"></i>
                        {{ __('Buat Piagam Proyek Sekarang') }}
                    </a>
                @else
                    <span class="inline-block text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-100 rounded-xl px-4 py-2.5">
                        <i class="fas fa-exclamation-triangle mr-1.5"></i> {{ __('Project Charter belum dibuat oleh Manager.') }}
                    </span>
                @endif
            </div>
        @else
            @php
                $suggestions = [];
                $isJsonSuggestions = false;
                if ($charter->ai_suggestions) {
                    $decoded = json_decode($charter->ai_suggestions, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $suggestions = $decoded;
                        $isJsonSuggestions = true;
                    }
                }
                $userRole = strtolower(Auth::user()->role);
                $showAiSection = ($userRole === 'manager');
            @endphp

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left Column: Details (2/3 Width) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Card 2: Ringkasan Eksekutif -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Ringkasan Eksekutif') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-regular fa-file-lines text-lg"></i>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-xs font-semibold text-slate-400 mb-1.5">{{ __('Tujuan Proyek') }}</h4>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                                    {{ $charter->project_purpose ?: __('Tidak ada detail tujuan proyek.') }}
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-400 mb-1.5">{{ __('Business Case') }}</h4>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                                    {{ $charter->business_case ?: __('Tidak ada detail kasus bisnis.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Objektif & Kriteria Sukses -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Sasaran & Kriteria Sukses') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-bullseye text-lg"></i>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-xs font-semibold text-slate-400 mb-1.5">{{ __('Objektif Utama') }}</h4>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-100 min-h-[100px]">
                                    {{ $charter->project_objectives ?: __('Tidak ada detail sasaran proyek.') }}
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-400 mb-1.5">{{ __('Kriteria Sukses') }}</h4>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-100 min-h-[100px]">
                                    {{ $charter->success_criteria ?: __('Tidak ada kriteria keberhasilan.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Ruang Lingkup & Milestone -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Ruang Lingkup & Milestone Utama') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-crop-simple text-lg"></i>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-xs font-semibold text-slate-400 mb-1.5">{{ __('Ringkasan Ruang Lingkup') }}</h4>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-100 min-h-[100px]">
                                    {{ $charter->scope_summary ?: __('Tidak ada ringkasan ruang lingkup.') }}
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-400 mb-1.5">{{ __('Ringkasan Milestone') }}</h4>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-100 min-h-[100px]">
                                    {{ $charter->milestone_summary ?: __('Tidak ada ringkasan milestone.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5: Milestone Aktual dari WBS/Timeline -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Milestone Aktual dari WBS/Timeline') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-flag text-lg"></i>
                            </div>
                        </div>

                        @if(isset($actualMilestones) && $actualMilestones->isNotEmpty())
                            <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-sm">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                            <th class="px-4 py-3">{{ __('Nama Milestone') }}</th>
                                            <th class="px-4 py-3">{{ __('Task WBS') }}</th>
                                            <th class="px-4 py-3">{{ __('Jadwal') }}</th>
                                            <th class="px-4 py-3 text-center">{{ __('Durasi') }}</th>
                                            <th class="px-4 py-3">{{ __('Predecessor') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                                        @foreach($actualMilestones as $milestone)
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-4 py-3.5">
                                                    <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                        <i class="fas fa-flag text-[9px]"></i>
                                                        {{ $milestone->milestone_name ?: '-' }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3.5 text-slate-800">
                                                    {{ $milestone->wbsItem ? $milestone->wbsItem->title : '-' }}
                                                </td>
                                                <td class="px-4 py-3.5 font-bold text-slate-600">
                                                    {{ $milestone->start_date ? $milestone->start_date->format('d M Y') : '-' }} 
                                                    s/d 
                                                    {{ $milestone->end_date ? $milestone->end_date->format('d M Y') : '-' }}
                                                </td>
                                                <td class="px-4 py-3.5 text-center font-bold text-slate-500">
                                                    {{ $milestone->duration_days }} {{ __('Hari') }}
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    @if($milestone->dependencyWbsItem)
                                                        <span class="text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200/60 inline-flex items-center font-bold">
                                                            <i class="fas fa-link mr-1"></i>
                                                            {{ Str::limit($milestone->dependencyWbsItem->title, 20) }}
                                                        </span>
                                                    @else
                                                        <span class="text-slate-400 italic text-[11px] font-medium">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-100 text-xs text-slate-500 flex items-center gap-2">
                                <i class="fas fa-info-circle text-slate-400 text-sm"></i>
                                <span>{{ __('Milestone aktual belum tersedia karena WBS/Timeline belum dibuat atau belum memiliki milestone.') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Card 6: Asumsi & Batasan + Stakeholders -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Asumsi & Batasan -->
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4 relative">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center justify-between mb-2">
                                <span>{{ __('Asumsi & Batasan') }}</span>
                                <i class="fa-solid fa-circle-exclamation text-slate-400 text-lg"></i>
                            </h3>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-400 mb-1.5">{{ __('Asumsi') }}</h4>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                                    {{ $charter->assumptions ?: __('Tidak ada detail asumsi.') }}
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-400 mb-1.5">{{ __('Batasan') }}</h4>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                                    {{ $charter->constraints ?: __('Tidak ada detail batasan.') }}
                                </div>
                            </div>
                        </div>

                        <!-- Stakeholders -->
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col relative">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-slate-800">{{ __('Pemangku Kepentingan Utama') }}</h3>
                                <i class="fa-solid fa-users text-slate-400 text-lg"></i>
                            </div>
                            <div class="flex-grow">
                                <h4 class="text-xs font-semibold text-slate-400 mb-1.5">{{ __('Ringkasan Pemangku Kepentingan') }}</h4>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-100 min-h-[175px] h-full">
                                    {{ $charter->stakeholder_summary ?: __('Tidak ada ringkasan pemangku kepentingan.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 7: Catatan & Umpan Balik Manager -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Catatan & Umpan Balik Manager') }}</h3>
                            <i class="fa-solid fa-comments text-slate-400 text-lg"></i>
                        </div>
                        <div class="p-4 rounded-xl text-sm leading-relaxed border {{ $charter->feedback_notes ? 'bg-amber-50/40 border-amber-100 text-amber-900 animate-pulse-once' : 'bg-slate-50/30 border-slate-100 text-slate-400 italic' }}">
                            @if($charter->feedback_notes)
                                <p class="whitespace-pre-wrap">{{ $charter->feedback_notes }}</p>
                            @else
                                <p>{{ __('Belum ada catatan atau umpan balik yang diberikan.') }}</p>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Right Column: Sidebar (1/3 Width) -->
                <div class="space-y-6">
                    <!-- Financial Box -->
                    <div class="bg-[#0B1329] p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 opacity-10 pointer-events-none">
                            <i class="fas fa-wallet text-8xl"></i>
                        </div>
                        
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Ringkasan Anggaran') }}</h3>
                        <div class="text-xl font-black tracking-tight text-white">
                            @if($charter->budget_summary !== null)
                                Rp {{ number_format($charter->budget_summary, 2, ',', '.') }}
                            @else
                                Rp -
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-400 mt-3 leading-relaxed">
                            {{ __('Anggaran definitif awal yang diusulkan dan disetujui dalam dokumen charter ini.') }}
                        </p>
                    </div>

                    <!-- Audit Metadata Box -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4 text-xs">
                        <h3 class="font-bold text-slate-800 pb-2 border-b border-slate-100">{{ __('Metadata Dokumen') }}</h3>
                        <div class="space-y-3.5">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 text-xs shadow-sm">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[9px] font-semibold uppercase tracking-wider">{{ __('Dibuat Oleh:') }}</span>
                                    <span class="font-bold text-slate-800 block mt-0.5">{{ $charter->creator ? $charter->creator->name : '-' }}</span>
                                    <span class="text-slate-400 block text-[9px] mt-0.5"><i class="fa-regular fa-clock mr-1"></i>{{ $charter->created_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 text-xs shadow-sm">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[9px] font-semibold uppercase tracking-wider">{{ __('Pembaruan Terakhir:') }}</span>
                                    <span class="font-bold text-slate-800 block mt-0.5">{{ $charter->updater ? $charter->updater->name : '-' }}</span>
                                    <span class="text-slate-400 block text-[9px] mt-0.5"><i class="fa-regular fa-clock mr-1"></i>{{ $charter->updated_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Suggestions Panel (Read-only view) -->
                    @if($showAiSection)
                        <div class="bg-white border border-slate-100 p-6 rounded-2xl shadow-sm relative overflow-hidden space-y-6">
                            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                            
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-sm">
                                        <i class="fa-solid fa-robot"></i>
                                    </span>
                                    {{ __('Rekomendasi AI') }}
                                </h3>
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-bold rounded uppercase tracking-wider">Beta</span>
                            </div>

                            @if($charter->ai_suggestions)
                                @if($isJsonSuggestions)
                                    <div class="space-y-4">
                                        @php
                                            $suggestionSpecs = [
                                                'project_purpose' => [
                                                    'label' => 'TUJUAN',
                                                    'relevance' => '85% relevan',
                                                    'bg' => 'bg-blue-50 text-blue-700 border-blue-100',
                                                ],
                                                'business_case' => [
                                                    'label' => 'BISNIS CASE',
                                                    'relevance' => '80% relevan',
                                                    'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                                ],
                                                'project_objectives' => [
                                                    'label' => 'SASARAN',
                                                    'relevance' => '90% relevan',
                                                    'bg' => 'bg-purple-50 text-purple-700 border-purple-100',
                                                ],
                                                'scope_summary' => [
                                                    'label' => 'RUANG LINGKUP',
                                                    'relevance' => '75% relevan',
                                                    'bg' => 'bg-sky-50 text-sky-700 border-sky-100',
                                                ],
                                                'success_criteria' => [
                                                    'label' => 'KRITERIA SUKSES',
                                                    'relevance' => '85% relevan',
                                                    'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                ],
                                                'assumptions' => [
                                                    'label' => 'ASUMSI',
                                                    'relevance' => '70% relevan',
                                                    'bg' => 'bg-teal-50 text-teal-700 border-teal-100',
                                                ],
                                                'constraints' => [
                                                    'label' => 'BATASAN',
                                                    'relevance' => '75% relevan',
                                                    'bg' => 'bg-rose-50 text-rose-700 border-rose-100',
                                                ],
                                                'stakeholder_summary' => [
                                                    'label' => 'STAKEHOLDER',
                                                    'relevance' => '80% relevan',
                                                    'bg' => 'bg-violet-50 text-violet-700 border-violet-100',
                                                ],
                                                'milestone_summary' => [
                                                    'label' => 'MILESTONE',
                                                    'relevance' => '85% relevan',
                                                    'bg' => 'bg-pink-50 text-pink-700 border-pink-100',
                                                ],
                                                'budget_summary' => [
                                                    'label' => 'ANGGARAN',
                                                    'relevance' => '90% relevan',
                                                    'bg' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                ]
                                            ];
                                        @endphp

                                        @foreach($suggestionSpecs as $field => $spec)
                                            @if(isset($suggestions[$field]))
                                                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold border {{ $spec['bg'] }}">
                                                                {{ $spec['label'] }}
                                                            </span>
                                                            <span class="text-[10px] text-slate-400 font-semibold">{{ $spec['relevance'] }}</span>
                                                        </div>
                                                    </div>
                                                    {{-- <p class="text-xs text-slate-600 leading-relaxed" id="ai-suggest-{{ $field }}">{{ $suggestions[$field] }}</p> --}}
                                                    <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line" id="ai-suggest-{{ $field }}">{{is_array($suggestions[$field])? implode("\n• ", $suggestions[$field]): $suggestions[$field]}}</p>
                                                    <div class="flex justify-end">
                                                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('ai-suggest-{{ $field }}').innerText); alert('Salin berhasil!');" 
                                                                class="px-2.5 py-1 bg-white border border-slate-200 text-slate-500 hover:text-slate-700 rounded-lg transition text-[9px] font-bold flex items-center gap-1">
                                                            <i class="fa-regular fa-copy"></i> Salin
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-indigo-50/20 p-4 rounded-xl border border-indigo-100 text-xs text-primaryText leading-relaxed">
                                        <div class="max-h-[300px] overflow-y-auto font-sans text-indigo-950 markdown-content markdown-content-sm shadow-inner" id="aiSuggestionsTextRaw">
                                            {!! str($charter->ai_suggestions)->markdown() !!}
                                        </div>
                                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('aiSuggestionsTextRaw').innerText); alert('Salin berhasil!');" 
                                                class="w-full mt-3 inline-flex items-center justify-center px-3 py-1.5 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-lg text-xs font-bold shadow-sm transition">
                                            <i class="fas fa-copy mr-1"></i> {{ __('Salin Semua Rekomendasi') }}
                                        </button>
                                    </div>
                                @endif
                                
                                @if($project->status === 'approved' && $charter->status === 'draft')
                                    <div class="mt-4 flex justify-end">
                                        <form action="{{ route('projects.charter.generate_ai', $project->id) }}" method="POST" class="ai-generate-form w-full">
                                            @csrf
                                            <button type="submit" class="btn-ai-generate w-full inline-flex items-center justify-center px-4 py-2.5 bg-white border border-dashed border-slate-300 hover:border-slate-400 text-slate-700 rounded-xl text-xs font-bold transition gap-1.5">
                                                <i class="fa-solid fa-wand-magic-sparkles text-indigo-500"></i> {{ __('Regenerate Rekomendasi AI') }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @else
                                <div class="border border-dashed border-indigo-100 bg-indigo-50/20 p-6 rounded-xl text-center">
                                    <p class="text-xs font-bold text-indigo-950 mb-1">{{ __('Rekomendasi AI Belum Digenerate') }}</p>
                                    <p class="text-[10px] text-indigo-600/70 max-w-xs mx-auto leading-relaxed mb-4">
                                        {{ __('AI Assistant dapat menganalisis deskripsi proyek dan proposal Anda untuk menghasilkan draf saran Project Charter yang relevan.') }}
                                    </p>
                                    
                                    @if($project->status === 'approved' && $charter->status === 'draft')
                                        <form action="{{ route('projects.charter.generate_ai', $project->id) }}" method="POST" class="ai-generate-form">
                                            @csrf
                                            <button type="submit" class="btn-ai-generate w-full inline-flex items-center justify-center px-4 py-2.5 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold shadow-sm transition gap-1.5">
                                                <i class="fas fa-magic"></i> {{ __('Generate Rekomendasi AI') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-block text-[10px] font-medium text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                                            {{ __('Regenerasi AI hanya aktif saat status draf.') }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        @endif
    </div>

    <!-- JS Loader Helper -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.ai-generate-form');
            forms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    const btn = form.querySelector('.btn-ai-generate');
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('opacity-75', 'cursor-not-allowed');
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> {{ __("Sedang Memproses AI...") }}';
                    }
                    
                    // Disable other buttons on the page to prevent multiple submissions
                    const allActionButtons = document.querySelectorAll('.btn-ai-generate, a, button[type="submit"]');
                    allActionButtons.forEach(actionBtn => {
                        if (actionBtn !== btn) {
                            if (actionBtn.tagName === 'A') {
                                actionBtn.classList.add('pointer-events-none', 'opacity-50');
                            } else {
                                actionBtn.disabled = true;
                                actionBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            }
                        }
                    });
                });
            });
        });
    </script>
</x-app-layout>
