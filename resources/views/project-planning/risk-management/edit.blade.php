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
                    @if($isPmo && $riskItems->count() > 0)
                        <form action="{{ route('projects.risk-management.finalize', $project->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi rencana manajemen risiko ini? Setelah finalized, seluruh alokasi risiko dan rencana mitigasi akan dikunci dan tidak dapat diubah lagi.');">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition gap-1.5">
                                <i class="fas fa-check-double"></i>
                                {{ __('Finalisasi Rencana Risiko') }}
                            </button>
                        </form>
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

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-xs flex items-center gap-2.5 shadow-sm">
                    <i class="fas fa-exclamation-circle text-rose-500"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-xs shadow-sm">
                    <div class="flex items-center gap-2 mb-2 font-bold">
                        <i class="fas fa-exclamation-triangle text-rose-500"></i>
                        <span>{{ __('Terdapat kesalahan input:') }}</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 text-xs font-semibold">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Finalization Status Banner -->
            @if($riskPlan && $riskPlan->status === 'finalized')
                <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-sm flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/15 text-white rounded-xl flex items-center justify-center text-xl shrink-0 shadow-inner">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold tracking-tight">{{ __('Perencanaan proyek selesai') }}</h4>
                            <p class="text-xs text-emerald-100 mt-1 leading-relaxed font-semibold">
                                {{ __('Semua modul perencanaan telah diverifikasi. Proyek siap untuk tahap eksekusi.') }}
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
                                {{ __('Rencana penanganan risiko proyek sedang disusun oleh PMO. Silakan tambahkan item risiko atau gunakan AI Assistant.') }}
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
                    <div>
                        <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#0B1329] hover:bg-slate-800 text-white rounded-xl text-xs font-black tracking-wider uppercase shadow-sm transition gap-1.5">
                            <i class="fas fa-plus"></i>
                            {{ __('TAMBAH RISIKO') }}
                        </button>
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
                                    <p class="text-xs text-slate-500 mb-4">{{ __('Rencana risiko Anda kosong. Tambahkan item risiko secara manual atau gunakan AI Assistant.') }}</p>
                                    <button type="button" onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl text-xs font-bold hover:bg-blue-600 hover:text-white transition gap-1.5 shadow-sm">
                                        <i class="fas fa-plus"></i>
                                        {{ __('Tambah Item Risiko') }}
                                    </button>
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
                                                    <td class="px-6 py-4 max-w-[140px]">
                                                        <div class="font-bold text-slate-800 text-sm truncate" title="{{ $item->risk_title }}">
                                                            {{ $item->risk_title }}
                                                        </div>
                                                        <div class="text-[10px] text-slate-400 font-semibold truncate mt-0.5" title="{{ $category }}">{{ $category }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 text-slate-500 font-medium max-w-[160px] truncate" title="{{ $item->impact }}">
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
                                                        <div class="inline-flex items-center gap-2">
                                                            <button type="button" onclick="selectRiskRow(this)" class="text-xs font-bold text-slate-500 hover:text-blue-600 transition mr-1">
                                                                {{ __('Detail') }}
                                                            </button>
                                                            
                                                            <!-- Edit Button -->
                                                            <button type="button" onclick='openEditModalFromBtn(this, {!! json_encode($item) !!})' class="w-7 h-7 flex items-center justify-center text-amber-600 bg-amber-50 border border-amber-100 hover:bg-amber-600 hover:text-white rounded-lg shadow-sm transition" title="{{ __('Ubah') }}">
                                                                <i class="fas fa-edit text-xs"></i>
                                                            </button>
                                                            
                                                            <!-- Delete Button -->
                                                            <form action="{{ route('projects.risk-management.items.delete', [$project->id, $item->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item risiko ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="w-7 h-7 flex items-center justify-center text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-600 hover:text-white rounded-lg shadow-sm transition" title="{{ __('Hapus') }}">
                                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                                </button>
                                                            </form>
                                                        </div>
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
                                    <p class="text-xs text-slate-400 italic px-4 leading-relaxed">{{ __('Belum ada rekomendasi AI. Silakan perbarui analisis di bawah.') }}</p>
                                </div>
                            @else
                                <div class="space-y-4 max-h-[340px] overflow-y-auto pr-1">
                                    @foreach($aiSuggestions as $idx => $sug)
                                        @php
                                            $sev = strtolower($sug['severity'] ?? 'medium');
                                            $badgeColor = 'bg-white/10 text-blue-300 border border-white/10';
                                            if ($sev === 'high') {
                                                $badgeColor = 'bg-rose-500/20 text-rose-300 border border-rose-500/30';
                                            }
                                        @endphp
                                        <div class="cursor-pointer p-4 bg-white/5 border border-white/5 hover:border-blue-400 hover:bg-white/10 rounded-xl transition duration-200 group"
                                             data-title="{{ $sug['risk_title'] ?? '' }}"
                                             data-description="{{ $sug['risk_description'] ?? '' }}"
                                             data-cause="{{ $sug['risk_cause'] ?? '' }}"
                                             data-impact="{{ $sug['impact'] ?? '' }}"
                                             data-probability="{{ $sug['probability'] ?? 'medium' }}"
                                             data-severity="{{ $sug['severity'] ?? 'medium' }}"
                                             data-mitigation="{{ $sug['mitigation_plan'] ?? '' }}"
                                             data-contingency="{{ $sug['contingency_plan'] ?? '' }}"
                                             data-owner="{{ $sug['risk_owner'] ?? '' }}"
                                             onclick="applySuggestionFromBtn(this)">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider {{ $badgeColor }}">
                                                    {{ $sev === 'high' ? 'Kritis' : 'Mitigasi Baru' }}
                                                </span>
                                                <span class="text-[9px] font-bold text-blue-300 hover:text-blue-200 flex items-center gap-1 transition">
                                                    {{ __('TERAPKAN') }} <i class="fas fa-chevron-right text-[7px] mt-0.5"></i>
                                                </span>
                                            </div>
                                            <h5 class="font-bold text-xs text-slate-100 leading-snug group-hover:text-blue-300 transition">{{ $sug['risk_title'] ?? '-' }}</h5>
                                            <p class="text-[10px] text-slate-400 leading-relaxed font-semibold mt-1">{{ $sug['risk_description'] ?? '-' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pt-4 border-t border-white/10 mt-4">
                                <form action="{{ route('projects.risk-management.generate_ai', $project->id) }}" method="POST" onsubmit="document.getElementById('ai-spinner').classList.remove('hidden'); document.getElementById('ai-btn-text').innerText='{{ __('Menganalisis...') }}';">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 bg-white/10 hover:bg-white/15 text-white rounded-xl text-xs font-black tracking-wider uppercase transition shadow-inner flex items-center justify-center gap-1.5">
                                        <i id="ai-spinner" class="fas fa-sync fa-spin hidden"></i>
                                        <i class="fas fa-sync text-[10px]"></i>
                                        <span id="ai-btn-text">{{ __('PERBARUI ANALISIS') }}</span>
                                    </button>
                                </form>
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

                        <!-- Notes Form Card -->
                        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-3">
                            <h4 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fas fa-sticky-note text-blue-600"></i>
                                {{ __('Catatan Rencana Risiko') }}
                            </h4>
                            <form action="{{ route('projects.risk-management.update', $project->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <textarea name="notes" rows="4" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 mb-3 placeholder-slate-400" placeholder="Masukkan catatan penanganan risiko...">{{ old('notes', $riskPlan->notes) }}</textarea>
                                <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                    {{ __('Simpan Catatan') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL: ADD RISK ITEM -->
    <div id="add-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background Overlay -->
            <div class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" aria-hidden="true" onclick="closeAddModal()"></div>
            <!-- Center Align -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl border border-slate-100 transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('projects.risk-management.items.add', $project->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-1.5">
                                <i class="fas fa-plus text-blue-600"></i>
                                {{ __('Tambah Potensi Risiko') }}
                            </h3>
                            <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-655 transition">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        <div class="space-y-4 max-h-[440px] overflow-y-auto pr-1">
                            <!-- Judul Risiko -->
                            <div>
                                <label for="add_risk_title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Judul Risiko') }}</label>
                                <input type="text" name="risk_title" id="add_risk_title" required class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50">
                            </div>

                            <!-- Deskripsi Risiko -->
                            <div>
                                <label for="add_risk_description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Deskripsi Risiko') }}</label>
                                <textarea name="risk_description" id="add_risk_description" required rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- Penyebab Risiko -->
                            <div>
                                <label for="add_risk_cause" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Faktor Penyebab (Cause) (Optional)') }}</label>
                                <textarea name="risk_cause" id="add_risk_cause" rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- Dampak -->
                            <div>
                                <label for="add_impact" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Dampak Risiko (Impact)') }}</label>
                                <textarea name="impact" id="add_impact" required rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- Probability & Severity & Status (Grid) -->
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label for="add_probability" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Probabilitas') }}</label>
                                    <select name="probability" id="add_probability" required class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="add_severity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Keparahan') }}</label>
                                    <select name="severity" id="add_severity" required class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="add_status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Status') }}</label>
                                    <select name="status" id="add_status" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                        <option value="open" selected>Open</option>
                                        <option value="mitigated">Mitigated</option>
                                        <option value="accepted">Accepted</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Rencana Mitigasi -->
                            <div>
                                <label for="add_mitigation_plan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Rencana Mitigasi (Preventif)') }}</label>
                                <textarea name="mitigation_plan" id="add_mitigation_plan" required rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- Rencana Kontingensi -->
                            <div>
                                <label for="add_contingency_plan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Rencana Kontingensi (Reaktif) (Optional)') }}</label>
                                <textarea name="contingency_plan" id="add_contingency_plan" rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- WBS Link & Owner (Grid) -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="add_related_wbs_item_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Tautkan WBS (Optional)') }}</label>
                                    <select name="related_wbs_item_id" id="add_related_wbs_item_id" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                        <option value="">-- {{ __('Tidak ditautkan') }} --</option>
                                        @foreach($wbsItems as $wbs)
                                            <option value="{{ $wbs->id }}">{{ $wbs->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="add_risk_owner" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Pemilik Risiko (Owner)') }}</label>
                                    <input type="text" name="risk_owner" id="add_risk_owner" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50">
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label for="add_notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Keterangan Lain (Opsional)') }}</label>
                                <textarea name="notes" id="add_notes" rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-700 rounded-xl text-xs font-bold transition">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            {{ __('Simpan Risiko') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: EDIT RISK ITEM -->
    <div id="edit-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background Overlay -->
            <div class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" aria-hidden="true" onclick="closeEditModal()"></div>
            <!-- Center Align -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl border border-slate-100 transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="edit-item-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-1.5">
                                <i class="fas fa-edit text-amber-500"></i>
                                {{ __('Ubah Potensi Risiko') }}
                            </h3>
                            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-655 transition">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        <div class="space-y-4 max-h-[440px] overflow-y-auto pr-1">
                            <!-- Judul Risiko -->
                            <div>
                                <label for="edit_risk_title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Judul Risiko') }}</label>
                                <input type="text" name="risk_title" id="edit_risk_title" required class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50">
                            </div>

                            <!-- Deskripsi Risiko -->
                            <div>
                                <label for="edit_risk_description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Deskripsi Risiko') }}</label>
                                <textarea name="risk_description" id="edit_risk_description" required rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- Penyebab Risiko -->
                            <div>
                                <label for="edit_risk_cause" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Faktor Penyebab (Cause) (Optional)') }}</label>
                                <textarea name="risk_cause" id="edit_risk_cause" rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- Dampak -->
                            <div>
                                <label for="edit_impact" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Dampak Risiko (Impact)') }}</label>
                                <textarea name="impact" id="edit_impact" required rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- Probability & Severity & Status (Grid) -->
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label for="edit_probability" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Probabilitas') }}</label>
                                    <select name="probability" id="edit_probability" required class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="edit_severity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Keparahan') }}</label>
                                    <select name="severity" id="edit_severity" required class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="edit_status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Status') }}</label>
                                    <select name="status" id="edit_status" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                        <option value="open">Open</option>
                                        <option value="mitigated">Mitigated</option>
                                        <option value="accepted">Accepted</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Rencana Mitigasi -->
                            <div>
                                <label for="edit_mitigation_plan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Rencana Mitigasi (Preventif)') }}</label>
                                <textarea name="mitigation_plan" id="edit_mitigation_plan" required rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- Rencana Kontingensi -->
                            <div>
                                <label for="edit_contingency_plan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Rencana Kontingensi (Reaktif) (Optional)') }}</label>
                                <textarea name="contingency_plan" id="edit_contingency_plan" rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>

                            <!-- WBS Link & Owner (Grid) -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="edit_related_wbs_item_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Tautkan WBS (Optional)') }}</label>
                                    <select name="related_wbs_item_id" id="edit_related_wbs_item_id" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                        <option value="">-- {{ __('Tidak ditautkan') }} --</option>
                                        @foreach($wbsItems as $wbs)
                                            <option value="{{ $wbs->id }}">{{ $wbs->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="edit_risk_owner" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Pemilik Risiko (Owner)') }}</label>
                                    <input type="text" name="risk_owner" id="edit_risk_owner" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50">
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label for="edit_notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Keterangan Lain (Opsional)') }}</label>
                                <textarea name="notes" id="edit_notes" rows="2" class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-700 rounded-xl text-xs font-bold transition">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS script for interactive details display and modals toggling -->
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

        function openAddModal() {
            const modal = document.getElementById('add-modal');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeAddModal() {
            const modal = document.getElementById('add-modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function openEditModalFromBtn(btn, item) {
            const modal = document.getElementById('edit-modal');
            const form = document.getElementById('edit-item-form');

            // Fill prefilled values
            document.getElementById('edit_risk_title').value = item.risk_title || '';
            document.getElementById('edit_risk_description').value = item.risk_description || '';
            document.getElementById('edit_risk_cause').value = item.risk_cause || '';
            document.getElementById('edit_impact').value = item.impact || '';
            document.getElementById('edit_probability').value = item.probability || 'medium';
            document.getElementById('edit_severity').value = item.severity || 'medium';
            document.getElementById('edit_status').value = item.status || 'open';
            document.getElementById('edit_mitigation_plan').value = item.mitigation_plan || '';
            document.getElementById('edit_contingency_plan').value = item.contingency_plan || '';
            document.getElementById('edit_related_wbs_item_id').value = item.related_wbs_item_id || '';
            document.getElementById('edit_risk_owner').value = item.risk_owner || '';
            document.getElementById('edit_notes').value = item.notes || '';

            // Update action route dynamically
            form.action = `/projects/{{ $project->id }}/risk-management/items/${item.id}`;

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function applySuggestionFromBtn(card) {
            // Open add modal
            openAddModal();

            // Populate form values from card data attributes
            document.getElementById('add_risk_title').value = card.getAttribute('data-title') || '';
            document.getElementById('add_risk_description').value = card.getAttribute('data-description') || '';
            document.getElementById('add_risk_cause').value = card.getAttribute('data-cause') || '';
            document.getElementById('add_impact').value = card.getAttribute('data-impact') || '';
            document.getElementById('add_probability').value = card.getAttribute('data-probability') || 'medium';
            document.getElementById('add_severity').value = card.getAttribute('data-severity') || 'medium';
            document.getElementById('add_mitigation_plan').value = card.getAttribute('data-mitigation') || '';
            document.getElementById('add_contingency_plan').value = card.getAttribute('data-contingency') || '';
            document.getElementById('add_risk_owner').value = card.getAttribute('data-owner') || '';
        }
    </script>
</x-app-layout>
