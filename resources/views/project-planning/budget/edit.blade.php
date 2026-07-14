<x-app-layout>
    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    @php
        $categories = [
            'human_resource' => ['label' => 'SDM', 'color_class' => 'bg-[#0B1329]', 'text_class' => 'text-[#0B1329]', 'bg_class' => 'bg-[#F1F5F9]', 'border_class' => 'border-slate-200', 'hex' => '#0B1329', 'icon' => 'fa-users'],
            'infrastructure' => ['label' => 'INFRASTRUKTUR', 'color_class' => 'bg-slate-500', 'text_class' => 'text-emerald-700', 'bg_class' => 'bg-emerald-50', 'border_class' => 'border-emerald-200', 'hex' => '#64748B', 'icon' => 'fa-server'],
            'tools' => ['label' => 'ALAT', 'color_class' => 'bg-slate-450', 'text_class' => 'text-purple-700', 'bg_class' => 'bg-purple-50', 'border_class' => 'border-purple-200', 'hex' => '#78889B', 'icon' => 'fa-laptop-code'],
            'operational' => ['label' => 'OPERASIONAL', 'color_class' => 'bg-slate-400', 'text_class' => 'text-amber-700', 'bg_class' => 'bg-amber-50', 'border_class' => 'border-amber-200', 'hex' => '#94A3B8', 'icon' => 'fa-route'],
            'contingency' => ['label' => 'CADANGAN', 'color_class' => 'bg-slate-300', 'text_class' => 'text-rose-700', 'bg_class' => 'bg-rose-50', 'border_class' => 'border-rose-200', 'hex' => '#CBD5E1', 'icon' => 'fa-shield-alt'],
            'other' => ['label' => 'LAINNYA', 'color_class' => 'bg-slate-200', 'text_class' => 'text-slate-700', 'bg_class' => 'bg-slate-50', 'border_class' => 'border-slate-200', 'hex' => '#E2E8F0', 'icon' => 'fa-box'],
        ];

        $userRole = strtolower(Auth::user()->role);
        $isManager = ($userRole === 'manager');
        $isDraft = $budgetPlan && $budgetPlan->status === 'draft';

        // Calculations for baseline differences
        $baselineDiff = 0;
        $baselinePercent = 0;
        $baselineText = '';
        $baselineBadgeClass = 'bg-slate-100 text-slate-600';
        if (!is_null($baselineBudget) && $baselineBudget > 0) {
            $baselineDiff = $totalRab - $baselineBudget;
            $baselinePercent = round(($baselineDiff / $baselineBudget) * 100, 1);
            if ($baselineDiff > 0) {
                $baselineText = "+" . $baselinePercent . "% Dari estimasi awal";
                $baselineBadgeClass = 'bg-rose-50 text-rose-600 border border-rose-100';
            } elseif ($baselineDiff < 0) {
                $baselineText = $baselinePercent . "% Dari estimasi awal";
                $baselineBadgeClass = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
            } else {
                $baselineText = "Sesuai dengan estimasi awal";
                $baselineBadgeClass = 'bg-slate-50 text-slate-600 border border-slate-200';
            }
        } else {
            $baselineText = "Baseline belum tersedia";
            $baselineBadgeClass = 'bg-slate-50 text-slate-400 border border-slate-200/60';
        }

        // Kategori alokasi dana progress bar
        $totalCostCalculated = $budgetPlan ? ($budgetPlan->budgetItems()->sum('total_cost') ?: 1) : 1;
        $categoryPercentages = [];
        foreach ($categories as $key => $cat) {
            $catSum = $budgetPlan ? $budgetPlan->budgetItems()->where('category', $key)->sum('total_cost') : 0;
            $percent = round(($catSum / $totalCostCalculated) * 100);
            $categoryPercentages[$key] = [
                'percent' => $percent,
                'sum' => $catSum,
                'label' => $cat['label'],
                'color_class' => $cat['color_class']
            ];
        }
    @endphp

    <div class="pl-4 pt-4 pb-12">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Back Navigation -->
            <div>
                <a href="{{ route('project-planning.budget.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 hover:text-slate-600 transition gap-1.5 uppercase tracking-wider">
                    <i class="fas fa-arrow-left text-[9px]"></i>
                    {{ __('KEMBALI KE DAFTAR') }}
                </a>
            </div>

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('PERENCANAAN PROYEK') }} / {{ __('KELOLA ANGGARAN (RAB)') }}</div>
                    <h2 class="font-extrabold text-2xl text-slate-800 leading-tight mt-1">
                        {{ __('Rencana Anggaran Biaya (RAB)') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ __('Kelola rincian anggaran belanja untuk Proyek ') }}<span class="font-bold text-slate-700">{{ $project->title }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('projects.show', $project->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs shadow-sm transition gap-1.5">
                        <i class="fas fa-project-diagram text-slate-400"></i>
                        {{ __('Hub Proyek') }}
                    </a>
                    
                    <a href="{{ route('projects.budget.edit', $project->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs shadow-sm transition gap-1.5">
                        <i class="fas fa-redo text-slate-400"></i>
                        {{ __('Reset Halaman') }}
                    </a>
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
                    <div class="flex items-center gap-2 mb-2 font-bold text-sm">
                        <i class="fas fa-exclamation-triangle text-rose-500"></i>
                        <span>{{ __('Terdapat kesalahan input:') }}</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 font-semibold text-slate-650">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Status Banner -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 shadow-sm flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-500 flex items-center justify-center text-sm shrink-0">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">{{ __('Mode Penyusunan Draf Anggaran') }}</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        {{ __('Anda dapat menambah, mengubah, dan menghapus item anggaran. Gunakan pagu baseline di bawah sebagai batas alokasi.') }}
                    </p>
                </div>
            </div>

            <!-- Area Finalisasi (Banners) -->
            @if($budgetItems->count() > 0)
                @if(is_null($baselineBudget))
                    <div class="p-5 bg-rose-50 border border-rose-100 text-rose-850 rounded-2xl text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-start sm:items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-lg shrink-0">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-sm text-slate-800">{{ __('RAB belum dapat difinalisasi') }}</h5>
                                <p class="text-xs text-rose-600 font-semibold mt-0.5">{{ __('RAB belum dapat difinalisasi karena baseline anggaran dari Proposal/Charter belum tersedia.') }}</p>
                            </div>
                        </div>
                        <button type="button" disabled class="opacity-55 cursor-not-allowed inline-flex items-center justify-center px-4 py-2.5 bg-slate-200 text-slate-400 rounded-xl text-xs font-bold gap-1.5 shrink-0">
                            <i class="fas fa-lock"></i>
                            {{ __('Finalisasi Terkunci') }}
                        </button>
                    </div>
                @elseif($totalRab > $baselineBudget)
                    <div class="p-5 bg-rose-50 border border-rose-100 text-rose-850 rounded-2xl text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-start sm:items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-lg shrink-0">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-sm text-slate-800">{{ __('RAB belum dapat difinalisasi') }}</h5>
                                <p class="text-xs text-rose-600 font-semibold mt-0.5">{{ __('RAB belum dapat difinalisasi karena total anggaran melebihi baseline awal.') }}</p>
                            </div>
                        </div>
                        <button type="button" disabled class="opacity-55 cursor-not-allowed inline-flex items-center justify-center px-4 py-2.5 bg-slate-200 text-slate-400 rounded-xl text-xs font-bold gap-1.5 shrink-0">
                            <i class="fas fa-lock"></i>
                            {{ __('Finalisasi Terkunci') }}
                        </button>
                    </div>
                @else
                    <div class="p-5 bg-emerald-50 border border-emerald-100 text-emerald-850 rounded-2xl text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-start sm:items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-sm text-slate-850">{{ __('RAB dapat difinalisasi') }}</h5>
                                <p class="text-xs text-emerald-600 font-semibold mt-0.5">{{ __('RAB dapat difinalisasi karena total anggaran masih sesuai baseline.') }}</p>
                            </div>
                        </div>
                        <form action="{{ route('projects.budget.finalize', $project->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi anggaran ini? Setelah finalized, seluruh rincian anggaran akan dikunci dan tidak dapat diubah lagi.');">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition gap-1.5">
                                <i class="fas fa-check-double text-[10px]"></i>
                                {{ __('Finalisasi Anggaran') }}
                            </button>
                        </form>
                    </div>
                @endif
            @endif

            <!-- Top Summary Cards (Redesign Layout) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Total Budget Card -->
                <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block flex items-center gap-1.5">
                            <i class="fa-solid fa-wallet text-[#0B1329]"></i>
                            {{ __('TOTAL ANGGARAN') }}
                            <button type="button" onclick="toggleBudgetVisibility()" class="text-slate-300 hover:text-slate-500 transition ml-1" title="Sembunyikan/Tampilkan Anggaran">
                                <i id="budget-eye-icon" class="fas fa-eye text-xs"></i>
                            </button>
                        </span>
                        <h3 id="total-budget-amount" data-value="Rp {{ number_format($totalRab, 0, ',', '.') }}" class="text-3xl font-black text-slate-800 tracking-tight transition-all duration-150">
                            Rp {{ number_format($totalRab, 0, ',', '.') }}
                        </h3>
                        <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-[10px] font-extrabold {{ $baselineBadgeClass }}">
                            @if(!is_null($baselineBudget) && $baselineDiff != 0)
                                @if($baselineDiff > 0)
                                    <i class="fas fa-arrow-up text-[8px]"></i>
                                @else
                                    <i class="fas fa-arrow-down text-[8px]"></i>
                                @endif
                            @endif
                            {{ $baselineText }}
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-700 border border-slate-100 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-coins text-slate-500"></i>
                    </div>
                </div>

                <!-- Category Allocation Distribution Card -->
                <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block flex items-center gap-1.5 mb-3">
                            <i class="fas fa-chart-pie text-slate-650"></i>
                            {{ __('DISTRIBUSI ALOKASI DANA') }}
                        </span>
                        <!-- Progress Stack Bar -->
                        <div class="w-full bg-slate-100 rounded-full h-3.5 overflow-hidden flex border border-slate-200/50">
                            @php $hasItems = false; @endphp
                            @foreach($categoryPercentages as $key => $item)
                                @if($item['percent'] > 0)
                                    @php $hasItems = true; @endphp
                                    <div class="{{ $item['color_class'] }} h-full transition-all duration-300" style="width: {{ $item['percent'] }}%" title="{{ $item['label'] }}: {{ $item['percent'] }}%"></div>
                                @endif
                            @endforeach
                            @if(!$hasItems)
                                <div class="bg-slate-300 h-full w-full" title="Belum ada data"></div>
                            @endif
                        </div>
                    </div>

                    <!-- Legends -->
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        @foreach($categoryPercentages as $key => $item)
                            @if($item['percent'] > 0 || $loop->iteration <= 4)
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full inline-block {{ $item['color_class'] }}"></span>
                                    <span>{{ $item['label'] }} <span class="text-slate-800 font-extrabold font-mono ml-0.5">{{ $item['percent'] }}%</span></span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Acuan Anggaran Detail Section (Informasi Baseline) -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200/60 p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-500 flex items-center justify-center text-sm shrink-0">
                        <i class="fas fa-shield-alt text-slate-500"></i>
                    </div>
                    <div>
                        <h5 class="text-xs font-bold text-slate-800">{{ __('Pagu Baseline Anggaran Proyek') }}</h5>
                        <p class="text-[10px] text-slate-400 font-semibold mt-0.5">
                            @if(!is_null($baselineBudget))
                                <span class="text-slate-600">Sumber: {{ $baselineSource }}</span> | Nominal Baseline: <span class="text-slate-600">Rp {{ number_format($baselineBudget, 0, ',', '.') }}</span>
                            @else
                                {{ __('Baseline anggaran belum tersedia dari Proposal/Charter.') }}
                            @endif
                        </p>
                    </div>
                </div>
                @if(!is_null($baselineBudget))
                    <div class="flex items-center gap-6 text-xs font-semibold text-slate-600">
                        <div class="flex flex-col text-right">
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Sisa Anggaran') }}</span>
                            <span class="font-extrabold mt-0.5 {{ $remainingBudget >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                Rp {{ number_format($remainingBudget, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Porsi Penggunaan') }}</span>
                            <span class="font-extrabold mt-0.5 text-slate-800">{{ $usagePercentage }}%</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Notes Section -->
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-3">
                <h4 class="font-bold text-xs uppercase text-slate-400 tracking-wider flex items-center gap-2">
                    <i class="fas fa-sticky-note text-[#0B1329]"></i>
                    {{ __('Catatan Rencana Anggaran') }}
                </h4>
                <form action="{{ route('projects.budget.update', $project->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <textarea name="notes" rows="2" class="w-full rounded-xl border-slate-200 text-xs shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-150 text-slate-700" placeholder="Masukkan catatan umum penyusunan RAB... ">{{ old('notes', $budgetPlan->notes) }}</textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            {{ __('Simpan Catatan') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Main Layout Container (Redesign Style) -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <!-- Title, Add Button, and Category Tabs -->
                <div class="px-6 pt-6 pb-4 border-b border-slate-100 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h4 class="font-extrabold text-slate-800 text-base">{{ __('Rincian Item Anggaran') }}</h4>
                            <p class="text-[11px] text-slate-400 font-medium">{{ __('Kelola dan filter rincian anggaran proyek berdasarkan kategori di bawah.') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Add Button (Black style in image reference) -->
                            <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center px-4 py-2 bg-[#0B1329] hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition gap-1.5">
                                <i class="fas fa-plus text-[9px]"></i>
                                {{ __('Tambah Item RAB') }}
                            </button>
                            <button type="button" class="p-1.5 border border-slate-200 rounded-lg text-slate-400 hover:text-slate-650 hover:bg-slate-50 text-xs transition" title="Filter">
                                <i class="fas fa-filter"></i>
                            </button>
                            <button type="button" class="p-1.5 border border-slate-200 rounded-lg text-slate-400 hover:text-slate-650 hover:bg-slate-50 text-xs transition" title="Export">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Category Filter Tabs -->
                    <div class="flex flex-wrap gap-1.5 pt-2">
                        <button type="button" id="tab-all" onclick="filterCategory('all')" 
                                class="filter-tab px-3 py-1.5 rounded-lg text-xs font-bold transition bg-[#0B1329] text-white">
                            {{ __('SEMUA') }}
                        </button>
                        @foreach($categories as $key => $cat)
                            <button type="button" id="tab-{{ $key }}" onclick="filterCategory('{{ $key }}')" 
                                    class="filter-tab px-3 py-1.5 rounded-lg text-xs font-bold transition bg-slate-50 text-slate-600 hover:bg-slate-100">
                                {{ $cat['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Items Table -->
                @if($budgetItems->isEmpty())
                    <div class="p-16 text-center">
                        <div class="w-12 h-12 bg-slate-50 text-slate-400 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm">
                            <i class="fas fa-wallet text-xl"></i>
                        </div>
                        <h5 class="font-bold text-sm text-slate-800 mb-1">{{ __('Item Anggaran Kosong') }}</h5>
                        <p class="text-xs text-slate-500 mb-4">{{ __('Belum ada rincian alokasi dana belanja untuk proyek ini.') }}</p>
                        <button type="button" onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-800 border border-slate-200 rounded-xl text-xs font-bold hover:bg-slate-250 transition gap-1.5">
                            <i class="fas fa-plus"></i>
                            {{ __('Tambahkan Item Pertama') }}
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="py-4 px-6">{{ __('KATEGORI') }}</th>
                                    <th class="py-4 px-6">{{ __('DESKRIPSI') }}</th>
                                    <th class="py-4 px-6 text-center">{{ __('SATUAN') }}</th>
                                    <th class="py-4 px-6 text-center">{{ __('QTY') }}</th>
                                    <th class="py-4 px-6 text-right">{{ __('HARGA SATUAN') }}</th>
                                    <th class="py-4 px-6 text-right">{{ __('TOTAL') }}</th>
                                    <th class="py-4 px-6 text-right">{{ __('AKSI') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-xs">
                                @foreach($budgetItems as $item)
                                    @php
                                        $catConfig = $categories[$item->category] ?? $categories['other'];
                                    @endphp
                                    <tr class="budget-item-row hover:bg-slate-50/30 transition duration-150" data-category="{{ $item->category }}">
                                        <td class="py-4 px-6">
                                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded text-[9px] font-bold border {{ $catConfig['bg_class'] }} {{ $catConfig['text_class'] }} {{ $catConfig['border_class'] }}">
                                                {{ $catConfig['label'] }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-slate-800 text-[13px]">{{ $item->description }}</div>
                                            @if($item->notes)
                                                <div class="text-[10px] text-slate-400 italic mt-1 flex items-center gap-1 font-medium">
                                                    <i class="far fa-comment"></i>
                                                    {{ $item->notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-center font-semibold text-slate-500">
                                            {{ $item->unit }}
                                        </td>
                                        <td class="py-4 px-6 text-center font-bold text-slate-700">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="py-4 px-6 text-right font-bold text-slate-600 font-mono">
                                            Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                                        </td>
                                        <td class="py-4 px-6 text-right font-extrabold text-slate-800 font-mono text-sm">
                                            Rp {{ number_format($item->total_cost, 0, ',', '.') }}
                                        </td>
                                        <td class="py-4 px-6 text-right pl-3">
                                            <div class="inline-flex gap-1.5 justify-end">
                                                <!-- Edit Button -->
                                                <button type="button" 
                                                        onclick='openEditModal({!! json_encode($item) !!})' 
                                                        class="p-1.5 text-amber-600 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-600 hover:text-white transition shadow-sm"
                                                        title="{{ __('Edit Item') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <!-- Delete Button -->
                                                <form action="{{ route('projects.budget.items.delete', [$project->id, $item->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item anggaran ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="p-1.5 text-rose-600 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-600 hover:text-white transition shadow-sm"
                                                            title="{{ __('Hapus Item') }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-[#0B1329] text-white font-extrabold text-sm border-t border-slate-800">
                                    <td colspan="5" class="py-4 px-6 uppercase tracking-wider text-right text-[10px] font-bold text-slate-400">{{ __('Total Anggaran Keseluruhan') }}</td>
                                    <td class="py-4 px-6 text-right font-mono text-[#38BDF8] text-base">Rp {{ number_format($totalRab, 0, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Footer Pagination / Stats -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500 font-medium">
                        <div id="pagination-stats">
                            {{ __('Menampilkan ') }}<span class="font-bold text-slate-700" id="visible-count">{{ $budgetItems->count() }}</span>{{ __(' dari ') }}<span class="font-bold text-slate-700">{{ $budgetItems->count() }}</span>{{ __(' item anggaran') }}
                        </div>
                        <div class="inline-flex gap-1">
                            <button type="button" disabled class="px-3 py-1 border border-slate-200 rounded-lg text-slate-400 bg-slate-50 cursor-not-allowed text-[11px] font-bold">Sebelumnya</button>
                            <button type="button" class="px-3 py-1 border border-slate-800 bg-slate-800 text-white rounded-lg text-[11px] font-bold">1</button>
                            <button type="button" disabled class="px-3 py-1 border border-slate-200 rounded-lg text-slate-400 bg-slate-50 cursor-not-allowed text-[11px] font-bold">Selanjutnya</button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Bottom Information Cards (Redesign Layout) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left: Rekening Proyek Card -->
                <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex items-start gap-4">
                    <div class="w-10 h-10 bg-[#EEF2F6] text-blue-600 rounded-xl flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="space-y-1">
                        <h5 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                            {{ __('Rekening Proyek') }}
                            <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded text-[9px] font-extrabold uppercase tracking-wider">
                                {{ __('AKTIF') }}
                            </span>
                        </h5>
                        <p class="text-xs text-slate-600 font-bold font-mono">
                            {{ __('BNI Virtual Account: 9882 1234 5678 9012') }}
                        </p>
                        <p class="text-[10px] text-slate-400 font-medium font-semibold">
                            {{ __('Status: Rekening virtual project terikat secara otomatis untuk penarikan anggaran.') }}
                        </p>
                    </div>
                </div>

                <!-- Right: Dokumen Pendukung Card -->
                <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex items-start gap-4">
                    <div class="w-10 h-10 bg-[#EEF2F6] text-blue-600 rounded-xl flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <div class="space-y-1">
                        <h5 class="text-xs font-bold text-slate-800">{{ __('Dokumen Pendukung') }}</h5>
                        <p class="text-xs text-slate-600 font-semibold">
                            {{ __('Unduh file RAB-') . strtoupper(str_replace(' ', '-', $project->title)) . '.xlsx' }}
                        </p>
                        <a href="#" onclick="alert('Mengunduh berkas rancangan anggaran belanja...'); return false;" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-800 transition uppercase tracking-wider">
                            {{ __('DOWNLOAD SEKARANG') }}
                            <i class="fas fa-arrow-down text-[9px]"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD BUDGET ITEM (Redesigned with enterprise clean inputs) -->
    <div id="add-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeAddModal()">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                <form action="{{ route('projects.budget.items.add', $project->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                                <i class="fas fa-plus text-slate-900"></i>
                                {{ __('Tambah Item Anggaran') }}
                            </h3>
                            <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 transition">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Kategori -->
                            <div>
                                <label for="add_category" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Kategori') }}</label>
                                <select name="category" id="add_category" required class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100">
                                    <option value="">-- {{ __('Pilih Kategori') }} --</option>
                                    @foreach($categories as $key => $cat)
                                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $cat['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <label for="add_description" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Deskripsi Pekerjaan / Kebutuhan') }}</label>
                                <input type="text" name="description" id="add_description" required value="{{ old('description') }}" placeholder="Contoh: Honor Senior System Analyst" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-semibold text-slate-700">
                            </div>

                            <!-- Quantity & Unit -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="add_quantity" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Quantity') }}</label>
                                    <input type="number" name="quantity" id="add_quantity" required min="1" value="{{ old('quantity', 1) }}" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-bold text-slate-700">
                                </div>
                                <div>
                                    <label for="add_unit" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Satuan (Unit)') }}</label>
                                    <input type="text" name="unit" id="add_unit" required value="{{ old('unit', 'Bulan') }}" placeholder="Contoh: Orang, Unit" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-semibold text-slate-700">
                                </div>
                            </div>

                            <!-- Unit Cost -->
                            <div>
                                <label for="add_unit_cost" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Harga Satuan (Rp)') }}</label>
                                <input type="number" name="unit_cost" id="add_unit_cost" required min="0" value="{{ old('unit_cost') }}" placeholder="Contoh: 5000000" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-mono font-bold text-slate-700">
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label for="add_notes" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Catatan Khusus (Opsional)') }}</label>
                                <textarea name="notes" id="add_notes" rows="2" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 text-slate-700" placeholder="Keterangan tambahan biaya... ">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-150 rounded-xl text-xs font-bold transition">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            {{ __('Simpan asdadsItem') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: EDIT BUDGET ITEM (Redesigned with enterprise clean inputs) -->
    <div id="edit-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeEditModal()">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                <form id="edit-item-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                                <i class="fas fa-edit text-amber-500"></i>
                                {{ __('Ubah Item Anggaran') }}
                            </h3>
                            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Kategori -->
                            <div>
                                <label for="edit_category" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Kategori') }}</label>
                                <select name="category" id="edit_category" required class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100">
                                    <option value="">-- {{ __('Pilih Kategori') }} --</option>
                                    @foreach($categories as $key => $cat)
                                        <option value="{{ $key }}">{{ $cat['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <label for="edit_description" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Deskripsi Pekerjaan / Kebutuhan') }}</label>
                                <input type="text" name="description" id="edit_description" required placeholder="Contoh: Honor Senior System Analyst" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-bold text-slate-700">
                            </div>

                            <!-- Quantity & Unit -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_quantity" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Quantity') }}</label>
                                    <input type="number" name="quantity" id="edit_quantity" required min="1" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-bold text-slate-700 font-bold">
                                </div>
                                <div>
                                    <label for="edit_unit" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Satuan (Unit)') }}</label>
                                    <input type="text" name="unit" id="edit_unit" required placeholder="Contoh: Orang" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-semibold text-slate-700">
                                </div>
                            </div>

                            <!-- Unit Cost -->
                            <div>
                                <label for="edit_unit_cost" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Harga Satuan (Rp)') }}</label>
                                <input type="number" id="edit_unit_cost" name="unit_cost" required min="0" placeholder="Contoh: 5000000" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-mono font-bold text-slate-700">
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label for="edit_notes" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Catatan Khusus (Opsional)') }}</label>
                                <textarea name="notes" id="edit_notes" rows="2" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 text-slate-700" placeholder="Keterangan tambahan biaya... "></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-150 rounded-xl text-xs font-bold transition">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VANILLA JS MODALS TOGGLER AND FILTERING -->
    <script>
        let isBudgetHidden = false;
        function toggleBudgetVisibility() {
            const el = document.getElementById('total-budget-amount');
            const icon = document.getElementById('budget-eye-icon');
            if (!el || !icon) return;
            if (isBudgetHidden) {
                el.textContent = el.getAttribute('data-value');
                icon.className = 'fas fa-eye text-xs';
                isBudgetHidden = false;
            } else {
                el.textContent = 'Rp ••••••••';
                icon.className = 'fas fa-eye-slash text-xs';
                isBudgetHidden = true;
            }
        }

        function openAddModal() {
            const modal = document.getElementById('add-modal');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        // Close Add Modal and clear any dirty form state
        function closeAddModal() {
            const modal = document.getElementById('add-modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function openEditModal(item) {
            const modal = document.getElementById('edit-modal');
            const form = document.getElementById('edit-item-form');
            
            // Set input values
            document.getElementById('edit_category').value = item.category;
            document.getElementById('edit_description').value = item.description;
            document.getElementById('edit_quantity').value = item.quantity;
            document.getElementById('edit_unit').value = item.unit;
            
            // Support database parsing representing decimals
            const costVal = parseFloat(item.unit_cost) || 0;
            document.getElementById('edit_unit_cost').value = Math.round(costVal);
            
            document.getElementById('edit_notes').value = item.notes || '';

            // Update form action dynamically
            form.action = `/projects/{{ $project->id }}/budget/items/${item.id}`;

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function filterCategory(category) {
            // Update active tab styles
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('bg-[#0B1329]', 'text-white');
                tab.classList.add('bg-slate-50', 'text-slate-600', 'hover:bg-slate-100');
            });
            const activeTab = document.getElementById('tab-' + category);
            if (activeTab) {
                activeTab.classList.remove('bg-slate-50', 'text-slate-600', 'hover:bg-slate-100');
                activeTab.classList.add('bg-[#0B1329]', 'text-white');
            }

            // Filter table rows
            let visibleCount = 0;
            document.querySelectorAll('.budget-item-row').forEach(row => {
                if (category === 'all' || row.dataset.category === category) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            // Update visible count text
            const countEl = document.getElementById('visible-count');
            if (countEl) {
                countEl.textContent = visibleCount;
            }
        }
    </script>
</x-app-layout>
