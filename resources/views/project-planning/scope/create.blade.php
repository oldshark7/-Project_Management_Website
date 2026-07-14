<x-app-layout>
    <x-slot name="header">
        <x-header-component :title="'Buat Project Scope: ' . $project->title" icon="fa-solid fa-sitemap text-blue-600 text-lg" />
    </x-slot>

    <div class="px-4 py-2">
        <!-- Back Link -->
        <div class="mb-4">
            <a href="{{ route('projects.show', $project->id) }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition gap-1.5">
                <i class="fas fa-arrow-left"></i>
                {{ __('Kembali ke Detail Proyek') }}
            </a>
        </div>

        <form action="{{ route('projects.scope.store', $project->id) }}" method="POST" id="scopeForm">
            @csrf

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start mb-24">
                
                <!-- Left Column: Navigation Sidebar (1/4 Width) -->
                <div class="lg:col-span-1 space-y-4 lg:sticky lg:top-4">
                    
                    <!-- Steps Navigation Card -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">{{ __('Tahapan Pengisian') }}</h3>
                        <nav class="space-y-1">
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
                            <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                {{ __('Draf Baru') }}
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Form Cards Stack (3/4 Width) -->
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
                            <!-- Judul Proyek (Read-Only) -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Judul Proyek') }}</label>
                                <input type="text" value="{{ $project->title }}" disabled 
                                       class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-bold text-slate-500 cursor-not-allowed">
                            </div>

                            <!-- Tujuan Utama (Business Objectives) -->
                            <div>
                                <label for="objective" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Tujuan Utama (Business Objectives) *') }}</label>
                                <textarea name="objective" id="objective" rows="3" 
                                          class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Jelaskan tujuan akhir proyek secara spesifik dan terukur...">{{ old('objective') }}</textarea>
                                @error('objective')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Deskripsi Singkat -->
                            <div>
                                <label for="scope_description" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Deskripsi Singkat *') }}</label>
                                <textarea name="scope_description" id="scope_description" rows="3" 
                                          class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Jelaskan ringkasan cakupan pekerjaan proyek secara umum...">{{ old('scope_description') }}</textarea>
                                @error('scope_description')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- In Scope -->
                            <div>
                                <label for="in_scope" class="block text-xs font-semibold text-emerald-700 mb-1.5 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-plus"></i> {{ __('IN-SCOPE (TERMASUK) *') }}
                                </label>
                                <textarea name="in_scope" id="in_scope" rows="5" 
                                          class="w-full px-4 py-3 bg-emerald-50/10 border border-slate-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Contoh:&#10;+ Modul Keuangan & Akuntansi&#10;+ Manajemen Inventaris & Gudang">{{ old('in_scope') }}</textarea>
                                @error('in_scope')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Out of Scope -->
                            <div>
                                <label for="out_of_scope" class="block text-xs font-semibold text-rose-700 mb-1.5 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-minus"></i> {{ __('OUT-OF-SCOPE (TIDAK TERMASUK) *') }}
                                </label>
                                <textarea name="out_of_scope" id="out_of_scope" rows="5" 
                                          class="w-full px-4 py-3 bg-rose-50/10 border border-slate-200 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Contoh:&#10;- Pemeliharaan Perangkat Keras&#10;- Pelatihan User di Luar Jakarta">{{ old('out_of_scope') }}</textarea>
                                @error('out_of_scope')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Deliverables -->
                            <div>
                                <label for="deliverables" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Hasil Kerja (Deliverables) *') }}</label>
                                <textarea name="deliverables" id="deliverables" rows="4" 
                                          class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Contoh:&#10;1. Dokumen Arsitektur Sistem&#10;2. Source Code Core Module">{{ old('deliverables') }}</textarea>
                                @error('deliverables')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Acceptance Criteria -->
                            <div>
                                <label for="acceptance_criteria" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Kriteria Penerimaan (Acceptance Criteria) *') }}</label>
                                <textarea name="acceptance_criteria" id="acceptance_criteria" rows="4" 
                                          class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Contoh:&#10;1. Lengkap dengan diagram ERD, disetujui CTO&#10;2. Lulus unit testing 90%">{{ old('acceptance_criteria') }}</textarea>
                                @error('acceptance_criteria')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Persyaratan Utama -->
                            <div>
                                <label for="main_requirements" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Persyaratan Utama (Optional)') }}</label>
                                <textarea name="main_requirements" id="main_requirements" rows="3" 
                                          class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Contoh:&#10;- Server AWS aktif&#10;- Lisensi ERP dibayar">{{ old('main_requirements') }}</textarea>
                                @error('main_requirements')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Asumsi -->
                            <div>
                                <label for="assumptions" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Asumsi (Optional)') }}</label>
                                <textarea name="assumptions" id="assumptions" rows="3" 
                                          class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Contoh:&#10;- Data legacy bersih&#10;- Stakeholder tersedia">{{ old('assumptions') }}</textarea>
                                @error('assumptions')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Batasan / Kendala -->
                            <div>
                                <label for="constraints" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Batasan / Kendala (Optional)') }}</label>
                                <textarea name="constraints" id="constraints" rows="3" 
                                          class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Contoh:&#10;- Budget max Rp 2M&#10;- Durasi 6 bulan">{{ old('constraints') }}</textarea>
                                @error('constraints')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Catatan Tambahan -->
                            <div>
                                <label for="notes" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ __('Catatan Tambahan (Optional)') }}</label>
                                <textarea name="notes" id="notes" rows="3" 
                                          class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl text-xs text-slate-800 transition placeholder-slate-400" 
                                          placeholder="Catatan tambahan penting lainnya terkait cakupan proyek...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Sticky Bottom Action Bar -->
            <div class="fixed bottom-0 left-0 right-0 md:left-60 bg-white/85 backdrop-blur-md border-t border-slate-100 p-4 flex flex-col sm:flex-row items-center justify-between gap-4 z-30 shadow-lg px-6 transition-all duration-300">
                <!-- Left: Info -->
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-semibold text-slate-500"><i class="fas fa-circle-info text-blue-500 mr-1.5"></i>{{ __('Lengkapi semua field wajib (*) sebelum melakukan finalisasi.') }}</span>
                </div>

                <!-- Right: Form actions -->
                <div class="flex flex-wrap items-center justify-end gap-2.5 w-full sm:w-auto">
                    <a href="{{ route('projects.show', $project->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-800 rounded-xl text-xs font-bold transition">
                        {{ __('Batal') }}
                    </a>
                    
                    <button type="submit" name="action" value="save" class="px-4 py-2 bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-800 rounded-xl text-xs font-bold transition">
                        {{ __('Simpan Draf') }}
                    </button>

                    <button type="submit" name="action" value="submit" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg shadow-blue-500/10 transition gap-1.5" onclick="return confirm('Apakah Anda yakin ingin memfinalisasi Project Scope ini? Data yang telah difinalisasi tidak dapat diubah lagi.');">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        {{ __('Finalisasi Scope') }}
                    </button>
                </div>
            </div>

        </form>
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
            const navLinks = document.querySelectorAll('nav a');
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
