<x-app-layout>
    <x-slot name="header">
        <x-header-component />
    </x-slot>

    <div class="px-4 py-2">
        <!-- Back Link -->
        <div class="mb-4">
            <a href="{{ route('project-planning.scope.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition gap-1.5">
                <i class="fas fa-arrow-left"></i>
                {{ __('Kembali ke Daftar Scope') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @php
            $userRole = strtolower(Auth::user()->role);
            $isManager = ($userRole === 'manager');
            $isDraft = ($scope && $scope->status === 'draft');
            $isFinalized = ($scope && $scope->status === 'finalized');
        @endphp

        <!-- WBS Readiness Indicator Banner -->
        @if($isFinalized)
            <div class="mb-6 p-5 rounded-2xl bg-gradient-to-r from-blue-500/10 via-indigo-500/5 to-white border border-blue-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-600/10 text-blue-600 rounded-2xl border border-blue-200/50 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-square-check"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-blue-900">{{ __('Cakupan Terdefinisi') }}</h4>
                        <p class="text-xs text-blue-755 mt-1 leading-relaxed font-semibold">
                            {{ __('Dokumen cakupan proyek Anda telah difinalisasi dan Siap digunakan untuk WBS (Work Breakdown Structure).') }}
                        </p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('projects.wbs.show', $project->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition gap-1.5 shrink-0">
                        {{ __('Generate WBS') }}
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        @else
            <div class="mb-6 p-5 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-100 text-slate-500 rounded-2xl border border-slate-200 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-regular fa-file-lines"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-800">{{ __('Draf Project Scope') }}</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        {{ __('Status dokumen masih berupa draf. Finalisasikan dokumen untuk mengunci data dan melanjutkan ke perencanaan WBS.') }}
                    </p>
                </div>
            </div>
        @endif

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start mb-12">
            
            <!-- Left Column: Navigation Sidebar (1/4 Width) -->
            <div class="lg:col-span-1 space-y-4 lg:sticky lg:top-4">
                
                <!-- Steps Navigation Card -->
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">{{ __('Tahapan Pengisian') }}</h3>
                    <nav id="scope-navigation"class="space-y-1">
                        <a href="#section-1" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-blue-600 bg-blue-50/50 transition">
                            <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold shadow-sm shadow-blue-500/10">1</span>
                            <span>{{ __('Tujuan & Deskripsi') }}</span>
                        </a>
                        <a href="#section-2" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 group-hover:bg-slate-200 flex items-center justify-center text-[10px] font-bold">2</span>
                            <span>{{ __('Batasan Lingkup') }}</span>
                        </a>
                        <a href="#section-3" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 group-hover:bg-slate-200 flex items-center justify-center text-[10px] font-bold">3</span>
                            <span>{{ __('Hasil & Kriteria') }}</span>
                        </a>
                        <a href="#section-4" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 group-hover:bg-slate-200 flex items-center justify-center text-[10px] font-bold">4</span>
                            <span>{{ __('Persyaratan & Asumsi') }}</span>
                        </a>
                    </nav>
                </div>

                <!-- Status Badge Card -->
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('Status Dokumen') }}</span>
                    <div class="flex">
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-lg text-[10px] font-bold uppercase tracking-wider border {{ $isFinalized ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ $isFinalized ? __('Draf Final - Siap Review') : __('Draft') }}
                        </span>
                    </div>
                </div>

            </div>

            <!-- Right Column: Details Stack (3/4 Width) -->
            <div class="lg:col-span-3 space-y-6">
                
                <!-- Section 1: Tujuan & Deskripsi -->
                <div id="section-1" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm scroll-mt-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-black shadow-sm shrink-0">
                            <i class="fa-regular fa-file-lines"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-850 uppercase tracking-wider">{{ __('Section 1: Tujuan & Deskripsi') }}</h3>
                            <p class="text-[11px] text-slate-400 leading-relaxed">{{ __('Definisikan mengapa proyek ini ada dan apa yang ingin dicapai.') }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Judul Proyek -->
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 mb-1">{{ __('Judul Proyek') }}</h4>
                            <div class="text-xs font-bold text-slate-800 bg-slate-50/50 px-4 py-2.5 rounded-xl border border-slate-100">
                                {{ $project->title }}
                            </div>
                        </div>

                        <!-- Tujuan Utama (Business Objectives) -->
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 mb-1">{{ __('Tujuan Utama (Business Objectives)') }}</h4>
                            <div class="text-xs text-slate-700 bg-slate-50/50 px-4 py-3 rounded-xl border border-slate-100 leading-relaxed whitespace-pre-wrap">
                                {{ $scope->objective }}
                            </div>
                        </div>

                        <!-- Deskripsi Singkat -->
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 mb-1">{{ __('Deskripsi Singkat') }}</h4>
                            <div class="text-xs text-slate-700 bg-slate-50/50 px-4 py-3 rounded-xl border border-slate-100 leading-relaxed whitespace-pre-wrap">
                                {{ $scope->scope_description }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Batasan Ruang Lingkup -->
                <div id="section-2" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm scroll-mt-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="w-7 h-7 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs font-black shadow-sm shrink-0">
                            <i class="fa-solid fa-compress-arrows-alt"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-850 uppercase tracking-wider">{{ __('Section 2: Batasan Ruang Lingkup') }}</h3>
                            <p class="text-[11px] text-slate-400 leading-relaxed">{{ __('Memisahkan apa yang termasuk dan tidak termasuk dalam pekerjaan.') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- In-Scope -->
                        <div class="bg-emerald-50/10 p-4 rounded-xl border border-slate-100">
                            <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-plus"></i> {{ __('IN-SCOPE (TERMASUK)') }}
                            </h4>
                            @php
                                $inScopeLines = array_filter(array_map('trim', explode("\n", $scope->in_scope)));
                            @endphp
                            <ul class="space-y-2">
                                @forelse($inScopeLines as $line)
                                    @php $cleanLine = ltrim($line, '+-• '); @endphp
                                    <li class="flex items-start gap-2 text-xs text-slate-700">
                                        <i class="fa-solid fa-plus text-emerald-500 mt-0.5 shrink-0 text-[10px]"></i>
                                        <span>{{ $cleanLine }}</span>
                                    </li>
                                @empty
                                    <li class="text-xs text-slate-400 italic">{{ __('Tidak ada detail pekerjaan.') }}</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- Out-of-Scope -->
                        <div class="bg-rose-50/10 p-4 rounded-xl border border-slate-100">
                            <h4 class="text-xs font-bold text-rose-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-minus"></i> {{ __('OUT-OF-SCOPE (TIDAK TERMASUK)') }}
                            </h4>
                            @php
                                $outOfScopeLines = array_filter(array_map('trim', explode("\n", $scope->out_of_scope)));
                            @endphp
                            <ul class="space-y-2">
                                @forelse($outOfScopeLines as $line)
                                    @php $cleanLine = ltrim($line, '+-• '); @endphp
                                    <li class="flex items-start gap-2 text-xs text-slate-700">
                                        <i class="fa-solid fa-minus text-rose-500 mt-0.5 shrink-0 text-[10px]"></i>
                                        <span>{{ $cleanLine }}</span>
                                    </li>
                                @empty
                                    <li class="text-xs text-slate-400 italic">{{ __('Tidak ada detail pekerjaan.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Hasil Kerja & Kriteria Penerimaan -->
                <div id="section-3" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm scroll-mt-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="w-7 h-7 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-black shadow-sm shrink-0">
                            <i class="fa-regular fa-square-check"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-850 uppercase tracking-wider">{{ __('Section 3: Hasil Kerja & Kriteria Penerimaan') }}</h3>
                            <p class="text-[11px] text-slate-400 leading-relaxed">{{ __('Output fisik dan standar kualitas yang harus dipenuhi.') }}</p>
                        </div>
                    </div>

                    <div class="border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                        @php
                            $deliverableLines = array_filter(array_map('trim', explode("\n", $scope->deliverables)));
                            $criteriaLines = array_filter(array_map('trim', explode("\n", $scope->acceptance_criteria)));
                            $maxRows = max(count($deliverableLines), count($criteriaLines));
                        @endphp
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="px-6 py-4 w-1/2">{{ __('DELIVERABLE (HASIL KERJA)') }}</th>
                                    <th class="px-6 py-4 w-1/2">{{ __('KRITERIA PENERIMAAN') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                                @for($i = 0; $i < $maxRows; $i++)
                                    @php
                                        $del = $deliverableLines[$i] ?? '-';
                                        $crit = $criteriaLines[$i] ?? '-';
                                        $cleanDel = preg_replace('/^\d+[\.\-\s]+/', '', $del);
                                        $cleanCrit = preg_replace('/^\d+[\.\-\s]+/', '', $crit);
                                    @endphp
                                    <tr class="hover:bg-slate-50/30 transition">
                                        <td class="px-6 py-4 font-bold text-slate-800">{{ $cleanDel }}</td>
                                        <td class="px-6 py-4 text-slate-500 leading-relaxed">{{ $cleanCrit }}</td>
                                    </tr>
                                @endfor
                                @if($maxRows === 0)
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-center text-slate-400 italic">{{ __('Belum ada data deliverable.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section 4: Persyaratan, Asumsi, Batasan -->
                <div id="section-4" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm scroll-mt-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="w-7 h-7 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-black shadow-sm shrink-0">
                            <i class="fa-solid fa-layer-group"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-850 uppercase tracking-wider">{{ __('Section 4: Persyaratan, Asumsi, Batasan') }}</h3>
                            <p class="text-[11px] text-slate-400 leading-relaxed">{{ __('Faktor-faktor eksternal yang mempengaruhi jalannya proyek.') }}</p>
                        </div>
                    </div>

                    @php
                        $reqLines = array_filter(array_map('trim', explode("\n", $scope->main_requirements)));
                        $asmpLines = array_filter(array_map('trim', explode("\n", $scope->assumptions)));
                        $constLines = array_filter(array_map('trim', explode("\n", $scope->constraints)));
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- PERSYARATAN -->
                        <div class="bg-slate-50/30 p-4 rounded-xl border border-slate-100/60">
                            <h4 class="text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-3">{{ __('Persyaratan Utama') }}</h4>
                            <ul class="space-y-1.5">
                                @forelse($reqLines as $line)
                                    @php $cleanLine = ltrim($line, '*-• '); @endphp
                                    <li class="text-[11px] text-slate-600 flex items-start gap-1.5">
                                        <span class="text-blue-500">•</span>
                                        <span>{{ $cleanLine }}</span>
                                    </li>
                                @empty
                                    <li class="text-[11px] text-slate-400 italic">{{ __('Tidak ada data.') }}</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- ASUMSI -->
                        <div class="bg-slate-50/30 p-4 rounded-xl border border-slate-100/60">
                            <h4 class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-3">{{ __('Asumsi') }}</h4>
                            <ul class="space-y-1.5">
                                @forelse($asmpLines as $line)
                                    @php $cleanLine = ltrim($line, '*-• '); @endphp
                                    <li class="text-[11px] text-slate-600 flex items-start gap-1.5">
                                        <span class="text-emerald-500">•</span>
                                        <span>{{ $cleanLine }}</span>
                                    </li>
                                @empty
                                    <li class="text-[11px] text-slate-400 italic">{{ __('Tidak ada data.') }}</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- BATASAN -->
                        <div class="bg-slate-50/30 p-4 rounded-xl border border-slate-100/60">
                            <h4 class="text-[10px] font-bold text-rose-600 uppercase tracking-wider mb-3">{{ __('Batasan') }}</h4>
                            <ul class="space-y-1.5">
                                @forelse($constLines as $line)
                                    @php $cleanLine = ltrim($line, '*-• '); @endphp
                                    <li class="text-[11px] text-slate-600 flex items-start gap-1.5">
                                        <span class="text-rose-500">•</span>
                                        <span>{{ $cleanLine }}</span>
                                    </li>
                                @empty
                                    <li class="text-[11px] text-slate-400 italic">{{ __('Tidak ada data.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions Form Contextual -->
                @if($isDraft && $isManager)
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                        <span class="text-[11px] font-semibold text-slate-500"><i class="fas fa-circle-info text-blue-500 mr-1.5"></i>{{ __('Draf cakupan proyek selesai dibuat? Silakan tinjau dan finalisasikan dokumen.') }}</span>
                        <div class="flex gap-2.5 w-full sm:w-auto justify-end">
                            <a href="{{ route('projects.scope.edit', $project->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-800 rounded-xl text-xs font-bold transition shadow-sm">
                                {{ __('Ubah Scope') }}
                            </a>

                            <form action="{{ route('projects.scope.finalize', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi Project Scope ini? Setelah finalized, data tidak dapat diubah lagi.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg shadow-blue-500/10 transition gap-1.5">
                                    <i class="fa-solid fa-lock text-[10px]"></i>
                                    {{ __('Finalisasi Scope') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Bottom Visuals Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                    <!-- Visualisasi Hubungan -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                            <i class="fa-solid fa-diagram-project text-slate-400"></i> {{ __('Visualisasi Hubungan') }}
                        </h3>
                        <div class="flex-grow flex flex-col items-center justify-center p-4 bg-slate-50/50 rounded-xl border border-slate-100 min-h-[140px]">
                            <div class="px-4 py-2 bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-bold uppercase rounded-lg shadow-sm">
                                {{ __('PROJECT SCOPE') }}
                            </div>
                            <div class="w-0.5 h-6 bg-slate-200"></div>
                            <div class="flex items-center gap-4">
                                <div class="px-3 py-1.5 bg-white border border-slate-200 text-slate-500 text-[9px] font-bold uppercase rounded-lg shadow-sm">
                                    {{ __('WBS Phase 1') }}
                                </div>
                                <div class="px-3 py-1.5 bg-white border border-slate-200 text-slate-500 text-[9px] font-bold uppercase rounded-lg shadow-sm">
                                    {{ __('WBS Phase 2') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolaborasi Terpadu -->
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl text-white shadow-md relative overflow-hidden flex flex-col justify-between min-h-[180px]">
                        <!-- Decorative circles -->
                        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                        <div class="absolute -left-6 -top-6 w-20 h-20 bg-white/15 rounded-full blur-lg"></div>

                        <div class="relative z-10">
                            <h4 class="text-sm font-extrabold tracking-tight mb-2">{{ __('Kolaborasi Terpadu') }}</h4>
                            <p class="text-[10px] text-indigo-100/90 leading-relaxed max-w-[200px]">
                                {{ __('Satu platform untuk menyelaraskan ekspektasi stakeholder dan tim pelaksana.') }}
                            </p>
                        </div>
                        <div class="relative z-10 flex items-center gap-2 mt-4 text-[9px] font-bold text-blue-300 uppercase tracking-wider">
                            <i class="fas fa-users text-sm"></i>
                            <span>Project Planning Hub</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Smooth scroll styling and script -->
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Update active state of sidebar links based on scroll/click
            const navLinks = document.querySelectorAll('#scope-navigation a');
            const sections = document.querySelectorAll('.scroll-mt-6');

            function updateActiveLink() {
                let currentSectionId = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop - 100;
                    if (window.scrollY >= sectionTop) {
                        currentSectionId = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    const href = link.getAttribute('href').substring(1);
                    const stepNum = link.querySelector('span:first-child');
                    if (href === currentSectionId) {
                        link.className = "group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-blue-600 bg-blue-50/50 transition";
                        if (stepNum) {
                            stepNum.className = "w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold shadow-sm shadow-blue-500/10";
                        }
                    } else {
                        link.className = "group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition";
                        if (stepNum) {
                            stepNum.className = "w-6 h-6 rounded-full bg-slate-100 text-slate-500 group-hover:bg-slate-200 flex items-center justify-center text-[10px] font-bold";
                        }
                    }
                });
            }

            window.addEventListener('scroll', updateActiveLink);
            updateActiveLink();
        });
    </script>
</x-app-layout>
