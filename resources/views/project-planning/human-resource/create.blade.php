<x-app-layout>
    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    <div class="pl-4 pt-4 pb-12">
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Back Navigation -->
            <div>
                <a href="{{ route('project-planning.human-resource.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 hover:text-slate-600 transition gap-1.5 uppercase tracking-wider">
                    <i class="fas fa-arrow-left text-[9px]"></i>
                    {{ __('KEMBALI KE DAFTAR') }}
                </a>
            </div>

            <!-- Header Section -->
            <div class="border-b border-slate-100 pb-5">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('PERENCANAAN PROYEK') }} / {{ __('INISIALISASI HR PLAN') }}</div>
                <h2 class="font-extrabold text-2xl text-slate-800 leading-tight mt-1">
                    {{ __('Inisialisasi Human Resource Plan') }}
                </h2>
                <div class="flex items-center gap-2 mt-2 text-xs text-slate-450 font-semibold">
                    <span>{{ __('Proyek:') }}</span>
                    <span class="font-bold text-slate-700 bg-slate-50 border border-slate-200/65 px-2.5 py-1 rounded-lg">{{ $project->title }}</span>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                <form action="{{ route('projects.human-resource.store', $project->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Info Alert Card -->
                    <div class="p-5 bg-blue-50/50 border border-blue-100 text-blue-800 rounded-xl flex items-start gap-4">
                        <div class="w-10 h-10 bg-blue-500/10 text-blue-600 rounded-xl flex items-center justify-center text-lg shrink-0">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <span class="font-bold text-xs uppercase tracking-wider text-slate-700 block">{{ __('Informasi Perencanaan SDM') }}</span>
                            <p class="mt-1 text-xs text-slate-505 leading-relaxed font-semibold">
                                {{ __('Setelah diinisialisasi, Anda dapat menambahkan rincian kebutuhan tim pelaksana proyek, mendefinisikan kriteria skill, deskripsi tugas pekerjaan, dan menetapkan penanggung jawab (PIC) langsung terhadap struktur kerja (WBS) yang ada.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-2">
                        <label for="notes" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            {{ __('Catatan Perencanaan SDM (Opsional)') }}
                        </label>
                        <textarea name="notes" id="notes" rows="4" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-slate-850 focus:ring focus:ring-slate-100 text-xs font-semibold text-slate-700 placeholder-slate-400/80 bg-slate-50/10" placeholder="Tuliskan catatan umum atau asumsi dasar perencanaan alokasi SDM proyek di sini...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-rose-600 text-xs mt-2 font-bold flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                        <a href="{{ route('project-planning.human-resource.index') }}" class="px-4 py-2.5 border border-slate-200 text-slate-500 hover:bg-slate-50 rounded-xl text-xs font-bold transition">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit" class="px-4 py-2.5 bg-[#0B1329] hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                            <i class="fas fa-check text-[10px]"></i>
                            {{ __('Inisialisasi HR Plan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
