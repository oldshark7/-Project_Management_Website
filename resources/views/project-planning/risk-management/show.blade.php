<x-app-layout>
    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    @php
        $userRole = strtolower(Auth::user()->role);
        $isPmo = ($userRole === 'pmo' || $userRole === 'project management officer');
        $isDraft = $riskPlan && $riskPlan->status === 'draft';
        
        if (!function_exists('getInitials')) {
            function getInitials($name) {
                $words = explode(' ', trim($name));
                $initials = '';
                foreach ($words as $w) {
                    $initials .= strtoupper(substr($w, 0, 1));
                    if (strlen($initials) >= 2) break;
                }
                return $initials ?: 'PM';
            }
        }

        // Decode AI Suggestions from riskPlan model
        $aiSuggestions = [];
        if ($riskPlan && $riskPlan->ai_suggestions) {
            try {
                $aiSuggestions = json_decode($riskPlan->ai_suggestions, true) ?: [];
            } catch (\Exception $e) {
                $aiSuggestions = [];
            }
        }

        // Calculate Project Health
        $healthPercent = $totalRisks > 0 ? round((($statusMitigated + $statusAccepted + $statusClosed) / $totalRisks) * 100) : 100;
        $healthText = 'Stabil. Sebagian besar risiko telah memiliki mitigasi aktif.';
        $healthTitle = 'Stabil';
        $healthColor = 'text-blue-600';
        $healthStroke = 'stroke-blue-600';
        if ($healthPercent >= 80) {
            $healthText = 'Sehat. Sebagian besar risiko telah memiliki mitigasi aktif.';
            $healthTitle = 'Sehat';
            $healthColor = 'text-emerald-500';
            $healthStroke = 'stroke-emerald-500';
        } elseif ($healthPercent < 50) {
            $healthText = 'Kritis. Segera lakukan penyusunan rencana mitigasi.';
            $healthTitle = 'Kritis';
            $healthColor = 'text-rose-500';
            $healthStroke = 'stroke-rose-500';
        }
        
        // SVG Circle Dash Calculations
        $radius = 12;
        $circumference = 2 * pi() * $radius;
        $strokeDashoffset = $circumference - ($healthPercent / 100) * $circumference;
    @endphp

    <div class="pl-4 pt-4 pb-12">
        <div class="max-w-6xl mx-auto space-y-6">
            
            <!-- Top Sub-Navigation Tabs -->
            <div class="flex items-center gap-6 border-b border-slate-100 mb-2 px-4">
                <a href="{{ route('projects.human-resource.show', $project->id) }}" class="pb-3 text-xs font-bold text-slate-400 hover:text-slate-600 transition">
                    {{ __('Human Resource Planning') }}
                </a>
                <a href="{{ route('projects.timeline.show', $project->id) }}" class="pb-3 text-xs font-bold text-slate-400 hover:text-slate-600 transition">
                    {{ __('Gantt Chart') }}
                </a>
                <a href="{{ route('projects.budget.show', $project->id) }}" class="pb-3 text-xs font-bold text-slate-400 hover:text-slate-600 transition">
                    {{ __('Budgeting') }}
                </a>
                <a href="{{ route('projects.risk-management.show', $project->id) }}" class="pb-3 text-xs font-bold text-blue-600 border-b-2 border-blue-600 transition">
                    {{ __('Risk Management') }}
                </a>
            </div>

            <!-- Back & Actions Toolbar -->
            <div class="flex items-center justify-between">
                <a href="{{ route('project-planning.risk-management.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 hover:text-slate-600 transition gap-1.5">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('Kembali ke Daftar') }}
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('projects.show', $project->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 hover:text-slate-900 rounded-xl text-xs font-bold shadow-sm transition gap-1.5">
                        <i class="fas fa-project-diagram text-slate-400"></i>
                        {{ __('Hub Proyek') }}
                    </a>
                    @if($isPmo && $isDraft)
                        <a href="{{ route('projects.risk-management.edit', $project->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-[#0B1329] hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition gap-1.5">
                            <i class="fas fa-edit"></i>
                            {{ __('Kelola Rencana Risiko') }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl text-xs flex items-center gap-2.5 shadow-sm">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 bg-blue-50 border border-blue-100 text-blue-800 rounded-xl text-xs flex items-center gap-2.5 shadow-sm">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
            @endif

            <!-- Status Banner -->
            @if($riskPlan && $riskPlan->status === 'finalized')
                <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-sm flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/15 text-white rounded-xl flex items-center justify-center text-xl shrink-0 shadow-inner">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold tracking-tight">{{ __('Perencanaan proyek selesai') }}</h4>
                            <p class="text-xs text-emerald-100 mt-1 leading-relaxed font-semibold">
                                {{ __('Rencana manajemen risiko telah difinalisasi. Proyek siap untuk tahap eksekusi.') }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('projects.show', $project->id) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white text-emerald-700 hover:bg-slate-50 font-bold rounded-xl text-xs shadow-sm transition gap-1.5 shrink-0">
                        {{ __('Lihat Hub Proyek') }}
                    </a>
                </div>
            @else
                <div class="p-5 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-sm flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/15 text-white rounded-xl flex items-center justify-center text-xl shrink-0 shadow-inner">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold tracking-tight">{{ __('Draf Rencana Manajemen Risiko (Belum Final)') }}</h4>
                            <p class="text-xs text-blue-100 mt-1 leading-relaxed font-semibold">
                                {{ __('Rencana penanganan risiko proyek sedang disusun oleh PMO. Silakan hubungi PMO jika ingin menambahkan mitigasi.') }}
                            </p>
                        </div>
                    </div>
                    @if($isPmo && $riskItems->count() > 0)
                        <form action="{{ route('projects.risk-management.finalize', $project->id) }}" method="POST" class="shrink-0" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi rencana manajemen risiko ini?');">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-white text-blue-600 hover:bg-slate-50 font-bold rounded-xl text-xs shadow-sm transition gap-1.5">
                                <i class="fas fa-check-circle"></i>
                                {{ __('Finalisasi Rencana Risiko') }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            @if(!$riskPlan)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-16 text-center">
                    <div class="w-16 h-16 bg-slate-50 text-slate-400 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-lg text-slate-800 mb-1">{{ __('Belum Ada Rencana Manajemen Risiko') }}</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mb-6">{{ __('Perencanaan risiko proyek belum diinisialisasi oleh PMO.') }}</p>
                </div>
            @else
                <!-- Content Dashboard Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ __('Manajemen Risiko') }}</h1>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Identifikasi dan pemantauan risiko proyek strategis') }}</p>
                    </div>
                </div>

                <!-- Three summary cards in a row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card 1: Total Risiko -->
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex flex-col justify-between h-32 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">
                                {{ __('TOTAL RISIKO') }}
                            </span>
                            <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-3xl font-black text-slate-800 tracking-tight">
                                {{ $totalRisks }}
                            </h3>
                            <span class="text-[10px] text-rose-500 font-semibold flex items-center gap-1 mt-1">
                                <i class="fas fa-chevron-up text-[8px]"></i>
                                {{ __('+2 dibanding bulan lalu') }}
                            </span>
                        </div>
                    </div>

                    <!-- Card 2: Probabilitas Tinggi -->
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex flex-col justify-between h-32 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">
                                {{ __('PROBABILITAS TINGGI') }}
                            </span>
                            <div class="w-8 h-8 bg-rose-50 text-rose-500 rounded-lg flex items-center justify-center text-sm">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-3xl font-black text-rose-600 tracking-tight">
                                {{ $probHigh }}
                            </h3>
                            <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1 mt-1">
                                <i class="fas fa-check text-[8px] text-emerald-500 bg-emerald-50 p-0.5 rounded-full"></i>
                                {{ __('Semua memiliki mitigasi') }}
                            </span>
                        </div>
                    </div>

                    <!-- Card 3: Risiko Tertutup -->
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex flex-col justify-between h-32 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">
                                {{ __('RISIKO TERTUTUP') }}
                            </span>
                            <div class="w-8 h-8 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-sm">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-3xl font-black text-purple-600 tracking-tight">
                                {{ $statusClosed }}
                            </h3>
                            <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1 mt-1">
                                <i class="fas fa-arrow-up text-[8px] text-blue-500"></i>
                                {{ __('Tingkat keberhasilan') }} {{ $healthPercent }}%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Main Layout Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    
                    <!-- Left Column: Table and details panel -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                            <!-- Section Title -->
                            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white">
                                <div>
                                    <h4 class="font-extrabold text-sm text-slate-800 uppercase tracking-wider">{{ __('Register Risiko Aktif') }}</h4>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button class="w-8 h-8 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition shadow-sm">
                                        <i class="fas fa-filter text-xs"></i>
                                    </button>
                                    <button class="w-8 h-8 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition shadow-sm">
                                        <i class="fas fa-download text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Table -->
                            @if($riskItems->isEmpty())
                                <div class="p-16 text-center">
                                    <div class="w-12 h-12 bg-slate-50 text-slate-400 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm">
                                        <i class="fas fa-shield-alt text-xl"></i>
                                    </div>
                                    <h5 class="font-bold text-sm text-slate-800 mb-1">{{ __('Alokasi Risiko Kosong') }}</h5>
                                    <p class="text-xs text-slate-500">{{ __('Belum ada rincian alokasi potensi risiko pelaksana untuk proyek ini.') }}</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                                <th class="px-6 py-4">{{ __('JUDUL RISIKO') }}</th>
                                                <th class="px-6 py-4">{{ __('DAMPAK') }}</th>
                                                <th class="px-6 py-4">{{ __('PROBABILITAS') }}</th>
                                                <th class="px-6 py-4">{{ __('TINGKAT KEPARAHAN') }}</th>
                                                <th class="px-6 py-4">{{ __('STATUS') }}</th>
                                                <th class="px-6 py-4 text-right pr-6">{{ __('AKSI') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="risk-table-body" class="divide-y divide-slate-50 text-xs">
                                            @foreach($riskItems as $idx => $item)
                                                @php
                                                    $sevVal = strtolower($item->severity);
                                                    $probVal = strtolower($item->probability);
                                                    
                                                    // Map probability label & styling
                                                    $probText = 'Sedang';
                                                    if ($probVal === 'high') {
                                                        $probText = 'Tinggi';
                                                    } elseif ($probVal === 'low') {
                                                        $probText = 'Rendah';
                                                    }
                                                    
                                                    // Map severity badge
                                                    $sevText = 'Menengah';
                                                    $sevBadge = 'bg-blue-50 text-blue-600 border border-blue-100';
                                                    if ($sevVal === 'high') {
                                                        $sevText = 'Kritis';
                                                        $sevBadge = 'bg-rose-50 text-rose-600 border border-rose-100';
                                                    } elseif ($sevVal === 'low') {
                                                        $sevText = 'Rendah';
                                                        $sevBadge = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
                                                    }
                                                    
                                                    // Map status dot & text
                                                    $statusText = 'Terencana';
                                                    $statusDot = 'bg-slate-400';
                                                    $stVal = strtolower($item->status);
                                                    if ($stVal === 'mitigated') {
                                                        $statusText = 'Mitigasi Aktif';
                                                        $statusDot = 'bg-rose-500';
                                                    } elseif ($stVal === 'accepted') {
                                                        $statusText = 'Monitoring';
                                                        $statusDot = 'bg-blue-500';
                                                    } elseif ($stVal === 'closed') {
                                                        $statusText = 'Selesai';
                                                        $statusDot = 'bg-emerald-500';
                                                    }
                                                    
                                                    // Category/WBS Item Title
                                                    $category = 'Operasional Proyek';
                                                    if ($item->wbsItem) {
                                                        $category = $item->wbsItem->title;
                                                    }
                                                @endphp
                                                <tr class="hover:bg-slate-55/40 transition duration-150 {{ $idx === 0 ? 'bg-blue-50/20 border-l-4 border-blue-600' : '' }}"
                                                    data-mitigation="{{ $item->mitigation_plan }}"
                                                    data-contingency="{{ $item->contingency_plan ?: __('Tidak ada rencana kontingensi.') }}">
                                                    <td class="px-6 py-4 max-w-[180px]">
                                                        <div class="font-bold text-slate-800 text-sm truncate" title="{{ $item->risk_title }}">
                                                            {{ $item->risk_title }}
                                                        </div>
                                                        <div class="text-[10px] text-slate-400 font-semibold truncate mt-0.5" title="{{ $category }}">{{ $category }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 text-slate-500 font-medium max-w-[200px] truncate" title="{{ $item->impact }}">
                                                        {{ $item->impact }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold border border-slate-200 bg-slate-50 text-slate-600">
                                                            {{ $probText }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $sevBadge }}">
                                                            {{ $sevText }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 font-semibold text-slate-700">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                                            <span>{{ $statusText }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 text-right pr-6">
                                                        <button type="button" onclick="selectRiskRow(this)" class="text-xs font-bold text-slate-500 hover:text-blue-600 transition">
                                                            {{ __('Detail') }}
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- Bottom Details Panel -->
                        @php
                            $firstItem = $riskItems->first();
                        @endphp
                        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-4">
                            <h4 class="font-black text-[10px] uppercase text-slate-400 tracking-wider">{{ __('DETIL STRATEGI MITIGASI TERPILIH') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5">
                                    <h5 class="font-bold text-xs text-slate-700">{{ __('Rencana Mitigasi (Preventif)') }}</h5>
                                    <p id="detail-mitigation" class="text-xs text-slate-500 leading-relaxed font-semibold whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100 min-h-24">
                                        {{ $firstItem ? $firstItem->mitigation_plan : __('Pilih baris risiko di atas untuk melihat detail mitigasi.') }}
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <h5 class="font-bold text-xs text-slate-700">{{ __('Rencana Kontingensi (Reaktif)') }}</h5>
                                    <p id="detail-contingency" class="text-xs text-slate-500 leading-relaxed font-semibold whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100 min-h-24">
                                        {{ $firstItem ? ($firstItem->contingency_plan ?: __('Tidak ada rencana kontingensi.')) : __('Pilih baris risiko di atas untuk melihat detail kontingensi.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- AI Recommendations Card -->
                        <div class="bg-[#0B1329] text-white rounded-2xl p-6 shadow-md relative overflow-hidden">
                            <!-- Background pattern light -->
                            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/5 rounded-full blur-xl pointer-events-none"></div>
                            
                            <div class="flex items-center gap-2 border-b border-white/10 pb-4 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white text-sm shadow-inner">
                                    <i class="fas fa-sparkles text-blue-300"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs tracking-wider uppercase text-blue-300">{{ __('Rekomendasi AI') }}</h4>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ __('Berdasarkan analisis risiko terbaru') }}</p>
                                </div>
                            </div>

                            <!-- Suggestions List -->
                            @if(empty($aiSuggestions))
                                <div class="text-center py-8">
                                    <div class="w-12 h-12 bg-white/5 text-slate-400 border border-white/10 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-lightbulb text-lg text-blue-300/60"></i>
                                    </div>
                                    <p class="text-xs text-slate-400 italic px-4 leading-relaxed">{{ __('Belum ada rekomendasi AI.') }}</p>
                                </div>
                            @else
                                <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
                                    @foreach($aiSuggestions as $idx => $sug)
                                        @php
                                            $sev = strtolower($sug['severity'] ?? 'medium');
                                            $badgeColor = 'bg-white/10 text-blue-300 border border-white/10';
                                            if ($sev === 'high') {
                                                $badgeColor = 'bg-rose-500/20 text-rose-300 border border-rose-500/30';
                                            }
                                        @endphp
                                        <div class="p-4 bg-white/5 border border-white/10 rounded-xl space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider {{ $badgeColor }}">
                                                    {{ $sev === 'high' ? 'Kritis' : 'Mitigasi Baru' }}
                                                </span>
                                            </div>
                                            <h5 class="font-bold text-xs text-slate-100 leading-snug">{{ $sug['risk_title'] ?? '-' }}</h5>
                                            <p class="text-[10px] text-slate-400 leading-relaxed font-semibold mt-1">{{ $sug['risk_description'] ?? '-' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pt-4 border-t border-white/10 mt-4">
                                <button type="button" class="w-full py-2.5 bg-white/10 hover:bg-white/15 text-white rounded-xl text-xs font-black tracking-wider uppercase transition shadow-inner flex items-center justify-center gap-1.5">
                                    {{ __('REKOMENDASI LENGKAP') }}
                                </button>
                            </div>
                        </div>

                        <!-- Kesehatan Risiko Proyek Card -->
                        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-4">
                            <h4 class="font-black text-[10px] uppercase text-slate-400 tracking-wider">{{ __('KESEHATAN RISIKO PROYEK') }}</h4>
                            <div class="flex items-center gap-4">
                                <!-- Radial chart -->
                                <div class="relative w-12 h-12 shrink-0 flex items-center justify-center">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                        <circle cx="18" cy="18" r="{{ $radius }}" fill="none" stroke="#F1F5F9" stroke-width="3"></circle>
                                        <circle cx="18" cy="18" r="{{ $radius }}" fill="none" class="{{ $healthStroke }} transition-all duration-500" stroke-width="3" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $strokeDashoffset }}"></circle>
                                    </svg>
                                    <span class="absolute font-bold text-slate-800 text-[10px]">{{ $healthPercent }}%</span>
                                </div>
                                <div>
                                    <h5 class="font-extrabold text-xs {{ $healthColor }}">{{ $healthTitle }}</h5>
                                    <p class="text-[10px] text-slate-400 font-semibold leading-normal mt-0.5">
                                        {{ $healthText }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Log Card -->
                        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-4">
                            <h4 class="font-black text-[10px] uppercase text-slate-400 tracking-wider">{{ __('LOG AKTIVITAS') }}</h4>
                            <div class="space-y-4">
                                <div class="flex gap-3 items-start text-xs">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <h6 class="font-bold text-slate-800">{{ __('Risiko Baru Ditambahkan') }}</h6>
                                        <p class="text-[10px] text-slate-400 font-semibold mt-0.5">10:45 • {{ __('Oleh') }} {{ $project->owner ? $project->owner->name : 'PM' }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-3 items-start text-xs">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <h6 class="font-bold text-slate-800">{{ __('Eskalasi Severity Kritis') }}</h6>
                                        <p class="text-[10px] text-slate-400 font-semibold mt-0.5">09:12 • {{ __('Automasi Sistem') }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-3 items-start text-xs">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <h6 class="font-bold text-slate-800">{{ __('Mitigasi Disetujui') }}</h6>
                                        <p class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ __('Kemarin') }} • {{ __('Direktur Proyek') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Card -->
                        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-3">
                            <h4 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fas fa-sticky-note text-blue-600"></i>
                                {{ __('Catatan Rencana Risiko') }}
                            </h4>
                            <p class="text-xs text-slate-500 leading-relaxed whitespace-pre-line font-semibold bg-slate-50 p-4 rounded-xl border border-slate-100">
                                {{ $riskPlan->notes ?: __('Tidak ada catatan khusus.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- JS script for interactive details display -->
    <script>
        function selectRiskRow(button) {
            const row = button.closest('tr');
            const mitigation = row.getAttribute('data-mitigation');
            const contingency = row.getAttribute('data-contingency');
            
            document.getElementById('detail-mitigation').innerText = mitigation;
            document.getElementById('detail-contingency').innerText = contingency;
            
            // Highlight selected row styling
            const tbody = document.getElementById('risk-table-body');
            Array.from(tbody.children).forEach(r => {
                r.classList.remove('bg-blue-50/20', 'border-l-4', 'border-blue-600');
            });
            row.classList.add('bg-blue-50/20', 'border-l-4', 'border-blue-600');
        }
    </script>
</x-app-layout>
