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
                <span class="text-slate-800 font-bold">Detail Proposal</span>
            </div>

            <!-- Right: Actions & User Info -->
            <div class="flex items-center gap-4 w-full sm:w-auto justify-end">
                @if($proposal)
                    <!-- Status Badge -->
                    @php
                        $statusClasses = [
                            'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
                            'submitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'reviewed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'revision_needed' => 'bg-rose-50 text-rose-700 border-rose-200',
                        ][$proposal->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    @endphp
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border {{ $statusClasses }}">
                        Status: {{ $proposal->status }}
                    </span>

                    <a href="{{ route('projects.proposal.download', $project->id) }}" 
                       class="px-4 py-2 bg-rose-600 hover:bg-rose-750 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-file-pdf"></i> {{ __('Download PDF') }}
                    </a>

                    @if(strtolower(Auth::user()->role) === 'manager' && $project->status === 'approved' && $proposal->status === 'draft')
                        <div class="flex items-center gap-2">
                            <a href="{{ route('projects.proposal.edit', $project->id) }}" 
                               class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                <i class="fas fa-edit text-slate-500"></i> {{ __('Ubah') }}
                            </a>
                            <form action="{{ route('projects.proposal.update', $project->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi proposal ini? Setelah difinalisasi, Anda tidak dapat mengedit lagi.');">
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

        @if(session('info'))
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                <span class="font-semibold">{{ session('info') }}</span>
            </div>
        @endif

        @if(!$proposal)
            <!-- Empty State Redesign -->
            <div class="bg-white p-12 rounded-2xl border border-slate-100 shadow-sm text-center max-w-xl mx-auto my-12">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 border border-blue-100 shadow-sm">
                    <i class="fa-regular fa-file-lines text-2xl"></i>
                </div>
                <h4 class="font-extrabold text-lg text-slate-800 mb-2">{{ __('Proposal Belum Dibuat') }}</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mb-8 leading-relaxed">
                    {{ __('Latar belakang, tujuan, perkiraan anggaran, dan dokumen inisiasi proposal lainnya belum didefinisikan untuk proyek ini.') }}
                </p>

                @if(strtolower(Auth::user()->role) === 'manager' && $project->status === 'approved')
                    <a href="{{ route('projects.proposal.create', $project->id) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#0B1329] hover:bg-[#1E293B] text-white font-bold rounded-xl text-xs transition shadow-md gap-2">
                        <i class="fas fa-plus text-[10px]"></i>
                        {{ __('Buat Proposal Sekarang') }}
                    </a>
                @else
                    <span class="inline-block text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-100 rounded-xl px-4 py-2.5">
                        <i class="fas fa-exclamation-triangle mr-1.5"></i> {{ __('Proposal belum dibuat oleh Manager.') }}
                    </span>
                @endif
            </div>
        @else
            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left Column: Details (2/3 Width) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Latar Belakang Card -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Latar Belakang') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-regular fa-circle-question text-lg"></i>
                            </div>
                        </div>
                        <div class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                            {{ $proposal->background ?: __('Tidak ada detail latar belakang.') }}
                        </div>
                    </div>

                    <!-- Tujuan Strategis Card -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Tujuan Strategis') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-bullseye text-lg"></i>
                            </div>
                        </div>
                        <div class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                            {{ $proposal->objectives ?: __('Tidak ada detail tujuan proyek.') }}
                        </div>
                    </div>

                    <!-- Kebutuhan Awal Card (Rendered as tags) -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Kebutuhan Awal') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-list-check text-lg"></i>
                            </div>
                        </div>
                        
                        @if($proposal->initial_needs)
                            @php
                                $tags = array_filter(array_map('trim', explode(',', $proposal->initial_needs)));
                            @endphp
                            <div class="flex flex-wrap gap-2">
                                @forelse($tags as $tag)
                                    <span class="inline-flex items-center px-3 py-1.5 bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold border border-slate-200/50 shadow-sm">
                                        {{ $tag }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400 italic">{{ __('Tidak ada detail kebutuhan awal.') }}</span>
                                @endforelse
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">{{ __('Tidak ada detail kebutuhan awal.') }}</span>
                        @endif
                    </div>

                    <!-- Gambaran Umum Proyek Card -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Gambaran Umum Proyek') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-globe text-lg"></i>
                            </div>
                        </div>
                        <div class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                            {{ $proposal->project_overview ?: __('Tidak ada gambaran umum proyek.') }}
                        </div>
                    </div>

                    <!-- Gambaran Ruang Lingkup Card -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Gambaran Ruang Lingkup (Scope)') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-crop-simple text-lg"></i>
                            </div>
                        </div>
                        <div class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                            {{ $proposal->scope_overview ?: __('Tidak ada gambaran ruang lingkup.') }}
                        </div>
                    </div>

                    <!-- Catatan & Umpan Balik Manager -->
                    @if($proposal->feedback_notes)
                        <div class="bg-amber-50/50 p-6 rounded-2xl border border-amber-200/60 shadow-sm border-l-4 border-l-amber-500">
                            <h3 class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <i class="fas fa-comment-dots text-sm"></i> {{ __('Catatan & Umpan Balik Manager') }}
                            </h3>
                            <div class="text-sm text-amber-950 whitespace-pre-line leading-relaxed">
                                {{ $proposal->feedback_notes }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column: Sidebar (1/3 Width) -->
                <div class="space-y-6">
                    <!-- Financial Box -->
                    <div class="bg-[#0B1329] p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 opacity-10 pointer-events-none">
                            <i class="fas fa-wallet text-8xl"></i>
                        </div>
                        
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Perkiraan Anggaran') }}</h3>
                        <div class="text-xl font-black tracking-tight text-white">
                            @if($proposal->estimated_budget !== null)
                                Rp {{ number_format($proposal->estimated_budget, 2, ',', '.') }}
                            @else
                                Rp -
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-400 mt-3 leading-relaxed">
                            {{ __('Anggaran indikatif awal untuk implementasi dan alokasi sumber daya proyek.') }}
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
                                    <span class="font-bold text-slate-800 block mt-0.5">{{ $proposal->creator ? $proposal->creator->name : '-' }}</span>
                                    <span class="text-slate-400 block text-[9px] mt-0.5"><i class="fa-regular fa-clock mr-1"></i>{{ $proposal->created_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 text-xs shadow-sm">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[9px] font-semibold uppercase tracking-wider">{{ __('Pembaruan Terakhir:') }}</span>
                                    <span class="font-bold text-slate-800 block mt-0.5">{{ $proposal->updater ? $proposal->updater->name : '-' }}</span>
                                    <span class="text-slate-400 block text-[9px] mt-0.5"><i class="fa-regular fa-clock mr-1"></i>{{ $proposal->updated_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Suggestions Panel (Read-only view) -->
                    @if(strtolower(Auth::user()->role) === 'manager')
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

                            @if($proposal->ai_suggestions)
                                @php
                                    $decoded = json_decode($proposal->ai_suggestions, true);
                                    $isJson = (json_last_error() === JSON_ERROR_NONE && is_array($decoded));
                                @endphp

                                @if($isJson)
                                    <div class="space-y-4">
                                        @php
                                            $suggestionSpecs = [
                                                'background' => [
                                                    'label' => 'EFEKTIVITAS',
                                                    'relevance' => '85% relevan',
                                                    'bg' => 'bg-blue-50 text-blue-700 border-blue-100',
                                                ],
                                                'objectives' => [
                                                    'label' => 'STRATEGIS',
                                                    'relevance' => '70% relevan',
                                                    'bg' => 'bg-purple-50 text-purple-700 border-purple-100',
                                                ],
                                                'initial_needs' => [
                                                    'label' => 'ALOKASI',
                                                    'relevance' => '75% relevan',
                                                    'bg' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                ],
                                                'project_overview' => [
                                                    'label' => 'DESKRIPTIF',
                                                    'relevance' => '65% relevan',
                                                    'bg' => 'bg-teal-50 text-teal-700 border-teal-100',
                                                ],
                                                'scope_overview' => [
                                                    'label' => 'CAKUPAN',
                                                    'relevance' => '80% relevan',
                                                    'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                ],
                                                'estimated_budget' => [
                                                    'label' => 'EFISIENSI',
                                                    'relevance' => '90% relevan',
                                                    'bg' => 'bg-rose-50 text-rose-700 border-rose-100',
                                                ]
                                            ];
                                        @endphp

                                        @foreach($suggestionSpecs as $field => $spec)
                                            @if(isset($decoded[$field]))
                                                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold border {{ $spec['bg'] }}">
                                                                {{ $spec['label'] }}
                                                            </span>
                                                            <span class="text-[10px] text-slate-400 font-semibold">{{ $spec['relevance'] }}</span>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-slate-600 leading-relaxed" id="ai-suggest-{{ $field }}">{{ $decoded[$field] }}</p>
                                                    <div class="flex justify-end">
                                                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('ai-suggest-{{ $field }}').innerText); alert('Salin berhasil!');" 
                                                                class="px-2 py-1 bg-white border border-slate-200 text-slate-500 hover:text-slate-700 rounded-lg transition text-[9px] font-bold flex items-center gap-1">
                                                            <i class="fa-regular fa-copy"></i> Salin
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-indigo-50/20 p-4 rounded-xl border border-indigo-100 text-xs text-primaryText leading-relaxed">
                                        <div class="max-h-[300px] overflow-y-auto font-sans text-indigo-950 markdown-content markdown-content-sm" id="aiSuggestionsTextRaw">
                                            {!! str($proposal->ai_suggestions)->markdown() !!}
                                        </div>
                                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('aiSuggestionsTextRaw').innerText); alert('Salin berhasil!');" 
                                                class="w-full mt-3 inline-flex items-center justify-center px-3 py-1.5 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-lg text-xs font-bold shadow-sm transition">
                                            <i class="fas fa-copy mr-1"></i> {{ __('Salin Semua Rekomendasi') }}
                                        </button>
                                    </div>
                                @endif
                                
                                @if($project->status === 'approved' && $proposal->status === 'draft')
                                    <div class="mt-4 flex justify-end">
                                        <form action="{{ route('projects.proposal.generate_ai', $project->id) }}" method="POST" class="ai-generate-form w-full">
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
                                        {{ __('AI Assistant dapat menganalisis deskripsi proyek untuk menghasilkan draf saran Project Proposal yang relevan.') }}
                                    </p>
                                    
                                    @if($project->status === 'approved' && $proposal->status === 'draft')
                                        <form action="{{ route('projects.proposal.generate_ai', $project->id) }}" method="POST" class="ai-generate-form">
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
