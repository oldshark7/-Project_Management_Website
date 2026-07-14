<x-app-layout>
    <div class="px-4 py-2">
        <form action="{{ route('projects.charter.store', $project->id) }}" method="POST" id="charterForm">
            @csrf

            <!-- Top Bar / Header Redesign -->
            <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <!-- Left: Breadcrumbs -->
                <div class="flex items-center gap-2 text-xs">
                    <a href="{{ route('projects.show', $project->id) }}" class="text-slate-400 hover:text-slate-600 transition font-medium">Inisiasi Proyek</a>
                    <span class="text-slate-300">/</span>
                    <a href="{{ route('projects.show', $project->id) }}" class="text-slate-400 hover:text-slate-600 transition font-semibold">Proyek #{{ $project->id }}</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-800 font-bold">Buat Piagam Proyek</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-400 font-semibold">Draf Baru</span>
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

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left Column: Form Cards (2/3 Width) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Card 1: Piagam Proyek Info Header -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
                        <div class="absolute top-6 right-6">
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-700 border border-gray-200 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                {{ __('Draf Baru') }}
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
                                          placeholder="Apa hasil akhir yang ingin dicapai?">{{ old('project_purpose') }}</textarea>
                                @error('project_purpose')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="business_case" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Business Case') }}</label>
                                <textarea name="business_case" id="business_case" rows="4" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Alasan strategis mengapa proyek ini dijalankan?">{{ old('business_case') }}</textarea>
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
                                          placeholder="Contoh: Digitalisasi HR">{{ old('project_objectives') }}</textarea>
                                @error('project_objectives')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="success_criteria" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Kriteria Sukses') }}</label>
                                <textarea name="success_criteria" id="success_criteria" rows="5" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Contoh: Efisiensi waktu 30%">{{ old('success_criteria') }}</textarea>
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
                                          placeholder="Asumsi (e.g. Lisensi tersedia)">{{ old('assumptions') }}</textarea>
                                @error('assumptions')
                                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="constraints" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Batasan') }}</label>
                                <textarea name="constraints" id="constraints" rows="4" 
                                          class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-sm text-slate-800 transition placeholder-slate-400 resize-none" 
                                          placeholder="Batasan (e.g. Budget maks 500jt)">{{ old('constraints') }}</textarea>
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
                                      placeholder="Daftar stakeholder kunci (misal: John Doe - Project Sponsor)">{{ old('stakeholder_summary') }}</textarea>
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
                                      placeholder="Ringkasan pekerjaan utama yang termasuk dan tidak termasuk dalam proyek...">{{ old('scope_summary') }}</textarea>
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
                                          placeholder="Tuliskan tahapan-tahapan penting proyek beserta target tanggal penyelesaiannya...">{{ old('milestone_summary') }}</textarea>
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
                                    <input type="number" name="budget_summary" id="budget_summary" step="0.01" min="0" value="{{ old('budget_summary') }}"
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

                        <!-- Empty State (Create View) -->
                        <div class="border border-dashed border-slate-200 bg-slate-50/50 p-6 rounded-2xl text-center">
                            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-robot text-lg"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-800 mb-1">{{ __('Belum Ada Rekomendasi AI') }}</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                {{ __('Tekan tombol "Simpan & Buat Rekomendasi" untuk menyimpan draf dan menganalisis Project Charter Anda.') }}
                            </p>
                        </div>

                        <!-- Action footer bottom of sidebar -->
                        <div class="pt-4 border-t border-slate-100 space-y-3.5">
                            <div class="text-[11px] text-slate-400 text-center leading-relaxed">
                                Butuh inspirasi lebih? Klik 'Generate' untuk analisis mendalam.
                            </div>
                            
                            <button type="submit" id="btn-generate-ai" name="action" value="generate_ai" 
                                    class="w-full py-2.5 bg-white border border-dashed border-slate-300 hover:border-slate-400 text-slate-700 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition">
                                <i class="fa-solid fa-wand-magic-sparkles text-indigo-500"></i>
                                {{ __('Simpan & Buat Rekomendasi') }}
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
    </script>
</x-app-layout>
