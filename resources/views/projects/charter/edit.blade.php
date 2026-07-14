<x-app-layout>
    <div class="px-4 py-2">
        <form action="{{ route('projects.charter.update', $project->id) }}" method="POST" id="charterForm">
            @csrf
            @method('PUT')

            <!-- Top Bar / Header Redesign -->
            <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <!-- Left: Breadcrumbs -->
                <div class="flex items-center gap-2 text-xs">
                    <a href="{{ route('projects.show', $project->id) }}" class="text-slate-400 hover:text-slate-600 transition font-medium">Inisiasi Proyek</a>
                    <span class="text-slate-300">/</span>
                    <a href="{{ route('projects.show', $project->id) }}" class="text-slate-400 hover:text-slate-600 transition font-semibold">Proyek #{{ $project->id }}</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-800 font-bold">Ubah Piagam Proyek</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-400 font-semibold">Draft #{{ $charter->id }}</span>
                </div>

                <!-- Right: Actions & User Info -->
                <div class="flex items-center gap-4 w-full sm:w-auto justify-end">
                    <!-- Form Actions -->
                    <div class="flex items-center gap-2">
                        <button type="submit" name="action" value="save" 
                                class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm">
                            {{ __('Simpan Draf') }}
                        </button>
                        <button type="submit" name="action" value="submit" 
                                class="px-4 py-2 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold transition shadow-md">
                            {{ __('Finalisasi') }}
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="hidden sm:block border-l border-slate-200 h-8"></div>

                    <!-- Profile Info -->
                    <div class="flex items-center gap-2.5">
                        <div class="text-right hidden md:block">
                            <p class="text-[10px] font-semibold text-slate-400 leading-none">Project Manager</p>
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

            @php
                $suggestions = [];
                $isJsonSuggestions = false;
                if ($charter->ai_suggestions) {
                    $decoded = json_decode($charter->ai_suggestions, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $suggestions = $decoded;
                        // dd($suggestions['project_objectives']);
                        $isJsonSuggestions = true;
                    }
                }
            @endphp

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left Column: Form Cards (2/3 Width) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Card 1: Piagam Proyek Info Header -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="absolute top-6 right-6">
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-700 border border-gray-200 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                {{ __('Status: ') . $charter->status }}
                            </span>
                        </div>
                        <h2 class="text-base font-extrabold text-slate-800 tracking-tight mb-1">
                            {{ __('Piagam Proyek (Project Charter)') }}
                        </h2>
                        <p class="text-xs text-slate-500">
                            {{ __('Lengkapi informasi dasar untuk memulai otorisasi proyek:') }} 
                            <span class="font-semibold text-slate-700">{{ $project->title }}</span>
                        </p>
                    </div>

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
                                <label for="project_purpose" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Tujuan Proyek') }}</label>
                                <textarea name="project_purpose" id="project_purpose" rows="4" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Apa hasil akhir yang ingin dicapai?">{{ old('project_purpose', $charter->project_purpose) }}</textarea>
                                @error('project_purpose')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="business_case" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Business Case') }}</label>
                                <textarea name="business_case" id="business_case" rows="4" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Alasan strategis mengapa proyek ini dijalankan?">{{ old('business_case', $charter->business_case) }}</textarea>
                                @error('business_case')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
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
                                <label for="project_objectives" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Objektif Utama') }}</label>
                                <textarea name="project_objectives" id="project_objectives" rows="5" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Contoh: Digitalisasi HR">{{ old('project_objectives', $charter->project_objectives) }}</textarea>
                                @error('project_objectives')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="success_criteria" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Kriteria Sukses') }}</label>
                                <textarea name="success_criteria" id="success_criteria" rows="5" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Contoh: Efisiensi waktu 30%">{{ old('success_criteria', $charter->success_criteria) }}</textarea>
                                @error('success_criteria')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Asumsi & Batasan -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Asumsi & Batasan') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-circle-exclamation text-lg"></i>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="assumptions" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Asumsi') }}</label>
                                <textarea name="assumptions" id="assumptions" rows="4" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Asumsi (e.g. Lisensi tersedia)">{{ old('assumptions', $charter->assumptions) }}</textarea>
                                @error('assumptions')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="constraints" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Batasan') }}</label>
                                <textarea name="constraints" id="constraints" rows="4" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Batasan (e.g. Budget maks 500jt)">{{ old('constraints', $charter->constraints) }}</textarea>
                                @error('constraints')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Card 5: Stakeholder Utama -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Pemangku Kepentingan Utama') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-users text-lg"></i>
                            </div>
                        </div>
                        <div>
                            <label for="stakeholder_summary" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Ringkasan Pemangku Kepentingan') }}</label>
                            <textarea name="stakeholder_summary" id="stakeholder_summary" rows="5" 
                                      class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                      placeholder="Daftar stakeholder kunci (misal: John Doe - Project Sponsor)">{{ old('stakeholder_summary', $charter->stakeholder_summary) }}</textarea>
                            @error('stakeholder_summary')
                                <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Card 6: Ringkasan Ruang Lingkup -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Batasan Ruang Lingkup') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-crop-simple text-lg"></i>
                            </div>
                        </div>
                        <div>
                            <label for="scope_summary" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Ringkasan Ruang Lingkup (Scope Summary)') }}</label>
                            <textarea name="scope_summary" id="scope_summary" rows="4" 
                                      class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                      placeholder="Ringkasan pekerjaan utama yang termasuk dan tidak termasuk dalam proyek...">{{ old('scope_summary', $charter->scope_summary) }}</textarea>
                            @error('scope_summary')
                                <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Card 7: Milestone & Anggaran -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Milestone & Anggaran') }}</h3>
                            <div class="text-slate-400">
                                <i class="fa-solid fa-flag text-lg"></i>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="milestone_summary" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Milestone Utama') }}</label>
                                <textarea name="milestone_summary" id="milestone_summary" rows="5" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Tuliskan tahapan-tahapan penting proyek beserta target tanggal penyelesaiannya...">{{ old('milestone_summary', $charter->milestone_summary) }}</textarea>
                                @error('milestone_summary')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="budget_summary" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Total Anggaran (IDR)') }}</label>
                                <div class="relative rounded-xl shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-slate-400 text-sm font-semibold">Rp</span>
                                    </div>
                                    <input type="number" name="budget_summary" id="budget_summary" step="0.01" min="0" value="{{ old('budget_summary', $charter->budget_summary) }}"
                                           class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 font-semibold transition" 
                                           placeholder="0.00">
                                </div>
                                @error('budget_summary')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: AI Sidebar (1/3 Width) -->
                <div class="space-y-6">
                    <!-- Asisten AI Box -->
                    <div class="bg-white border border-slate-100 p-6 rounded-2xl shadow-sm relative overflow-hidden space-y-6">
                        <!-- Top decorative accent -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                        
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-sm">
                                    <i class="fa-solid fa-robot"></i>
                                </span>
                                {{ __('Asisten AI') }}
                            </h3>
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-bold rounded uppercase tracking-wider">Beta</span>
                        </div>

                        <!-- Information Banner -->
                        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3.5 flex gap-2.5 items-start">
                            <i class="fa-solid fa-circle-info text-blue-500 text-sm mt-0.5"></i>
                            <p class="text-xs text-blue-800 leading-relaxed">
                                Saya sedang menganalisis draf Anda. Butuh saran cerdas untuk meningkatkan kualitas Project Charter Anda?
                            </p>
                        </div>

                        <!-- Section Title -->
                        <div class="text-[10px] font-extrabold text-slate-400 tracking-wider uppercase">
                            {{ __('Saran Penulisan') }}
                        </div>

                        <!-- Suggestions Cards list -->
                        @if($charter->ai_suggestions && $isJsonSuggestions)
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
                                        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm space-y-3">
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
                                            <div class="flex gap-2">
                                                <button type="button" onclick="useAiSuggestion('{{ $field }}')" 
                                                        class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-lg text-[10px] font-bold shadow-sm transition">
                                                    <i class="fas fa-check"></i> {{ __('Terapkan') }}
                                                </button>
                                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('ai-suggest-{{ $field }}').innerText); alert('Salin berhasil!');" 
                                                        class="px-2.5 py-2 bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:border-slate-300 rounded-lg transition" title="Salin">
                                                    <i class="fa-regular fa-copy text-[11px]"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @elseif($charter->ai_suggestions)
                            <div class="bg-indigo-50/20 p-4 rounded-xl border border-indigo-100 text-xs text-primaryText leading-relaxed">
                                <div class="max-h-[300px] overflow-y-auto font-sans text-indigo-950 markdown-content markdown-content-sm shadow-inner" id="aiSuggestionsTextRaw">
                                    {!! str($charter->ai_suggestions)->markdown() !!}
                                </div>
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('aiSuggestionsTextRaw').innerText); alert('Salin berhasil!');" 
                                        class="w-full mt-3 inline-flex items-center justify-center px-3 py-1.5 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-lg text-xs font-bold shadow-sm transition">
                                    <i class="fas fa-copy mr-1"></i> {{ __('Salin Semua Rekomendasi') }}
                                </button>
                            </div>
                        @else
                            <div class="border border-dashed border-slate-200 bg-slate-50/50 p-6 rounded-2xl text-center">
                                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-robot text-lg"></i>
                                </div>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">{{ __('Belum Ada Rekomendasi AI') }}</h4>
                                <p class="text-[11px] text-slate-500 leading-relaxed">
                                    {{ __('Tekan tombol di bawah untuk menghasilkan analisis draf charter.') }}
                                </p>
                            </div>
                        @endif

                        <!-- Action footer bottom of sidebar -->
                        <div class="pt-4 border-t border-slate-100 space-y-3.5">
                            <div class="text-[11px] text-slate-400 text-center leading-relaxed">
                                Butuh inspirasi lebih? Klik 'Generate' untuk analisis mendalam.
                            </div>
                            
                            <button type="submit" id="btn-generate-ai" name="action" value="generate_ai" 
                                    class="w-full py-2.5 bg-white border border-dashed border-slate-300 hover:border-slate-400 text-slate-700 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition">
                                <i class="fa-solid fa-wand-magic-sparkles text-indigo-500"></i>
                                {{ __('Generate Rekomendasi AI') }}
                            </button>

                            <!-- Warning Disclaimer -->
                            <div class="flex items-center justify-center gap-1.5 text-[10px] text-slate-400 italic">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>Saran AI perlu ditinjau kembali sebelum finalisasi.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Project Chart Mockup -->
                    <div class="bg-slate-900 text-white p-5 rounded-2xl relative overflow-hidden shadow-md h-44 flex flex-col justify-between">
                        <div class="absolute inset-0 opacity-20 pointer-events-none flex items-end">
                            <svg class="w-full h-24 text-blue-400" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <path d="M0,80 Q25,40 50,65 T100,25 L100,100 L0,100 Z" fill="currentColor"></path>
                            </svg>
                        </div>
                        <div class="relative z-10">
                            <span class="text-[9px] font-bold text-blue-400 uppercase tracking-wider block mb-1">ANALISIS VISUAL PROYEK</span>
                            <h4 class="text-sm font-extrabold tracking-tight">Proyeksi ROI 24 Bulan</h4>
                        </div>
                        <div class="relative z-10 flex justify-between items-end">
                            <span class="text-[10px] text-slate-400">Estimasi Efisiensi</span>
                            <span class="text-lg font-black text-blue-400">+34.5%</span>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- JS Helper Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('charterForm');
            const btnGenerate = document.getElementById('btn-generate-ai');
            
            if (form && btnGenerate) {
                btnGenerate.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Create a hidden input to preserve the action value
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'action';
                    hiddenInput.value = 'generate_ai';
                    form.appendChild(hiddenInput);

                    // Disable button and show loading state
                    btnGenerate.disabled = true;
                    btnGenerate.classList.add('opacity-75', 'cursor-not-allowed');
                    btnGenerate.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5 text-indigo-500"></i> {{ __("Sedang Memproses AI...") }}';
                    
                    // Disable other buttons to prevent concurrent action
                    const otherButtons = form.querySelectorAll('button[type="submit"]:not(#btn-generate-ai)');
                    otherButtons.forEach(btn => {
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                    });

                    // Submit the form
                    form.submit();
                });
            }
        });

        // AI Suggestion copy helper function
        function useAiSuggestion(fieldId) {
            const textElement = document.getElementById('ai-suggest-' + fieldId);
            const target = document.getElementById(fieldId);
            if (textElement && target) {
                let text = textElement.innerText.trim();
                
                if (target.type === 'number') {
                    let numericOnly = text.replace(/rp/gi, '').replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.]/g, '');
                    let matched = numericOnly.match(/\d+(\.\d+)?/);
                    if (matched) {
                        target.value = matched[0];
                    } else {
                        alert("Tidak dapat mengekstrak angka otomatis dari saran. Silakan masukkan secara manual.");
                        return;
                    }
                } else {
                    target.value = text;
                }
                target.dispatchEvent(new Event('input'));
                
                // Visual highlight effect
                target.classList.add('ring-2', 'ring-indigo-500', 'border-indigo-500');
                setTimeout(() => {
                    target.classList.remove('ring-2', 'ring-indigo-500', 'border-indigo-500');
                }, 1500);
            }
        }
    </script>
</x-app-layout>
