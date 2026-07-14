<x-app-layout>
    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    <div class="pl-4 pt-4 pb-12">
        <div class="max-w-3xl mx-auto space-y-6">
            
            <!-- Back Navigation -->
            <div>
                <a href="{{ route('project-planning.risk-management.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 hover:text-slate-600 transition gap-1.5">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('Kembali ke Daftar') }}
                </a>
            </div>

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-5 gap-4">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('PERENCANAAN PROYEK') }} / {{ __('MANAJEMEN RISIKO') }}</div>
                    <h2 class="font-extrabold text-2xl text-slate-800 leading-tight mt-1">
                        {{ __('Inisialisasi Risk Management Plan') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ __('Buat draft perencanaan manajemen risiko untuk proyek ini.') }}
                    </p>
                    <div class="flex items-center gap-2 mt-2.5 text-xs text-slate-400 font-medium">
                        <span>{{ __('Proyek:') }}</span>
                        <span class="font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200/50 shadow-sm">{{ $project->title }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                <form action="{{ route('projects.risk-management.store', $project->id) }}" method="POST">
                    @csrf

                    <!-- Info Alert Card -->
                    <div class="mb-6 p-5 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-700 text-white shadow-md flex items-start gap-4">
                        <div class="w-12 h-12 bg-white/15 text-white rounded-xl flex items-center justify-center text-xl shrink-0 shadow-inner">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold tracking-tight">{{ __('Informasi Inisialisasi') }}</h4>
                            <p class="text-xs text-blue-100 mt-1 leading-relaxed font-semibold">
                                {{ __('Inisialisasi ini akan membuat draf Rencana Manajemen Risiko baru untuk proyek ini. Setelah diinisialisasi, Anda (PMO) dapat mulai memetakan potensi risiko proyek, menetapkan peluang (probability), keparahan (severity), pemilik risiko (risk owner), menautkannya dengan tugas WBS, serta memanfaatkan AI Assistant untuk memberikan rekomendasi risiko awal.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            {{ __('Catatan Rencana Manajemen Risiko (Opsional)') }}
                        </label>
                        <textarea name="notes" id="notes" rows="4" 
                                  class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50"
                                  placeholder="Tuliskan catatan umum atau filosofi manajemen risiko untuk proyek ini di sini...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-rose-600 text-xs mt-1.5 font-bold flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                        <a href="{{ route('project-planning.risk-management.index') }}" class="px-4 py-2.5 border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 rounded-xl text-xs font-bold transition">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                            <i class="fas fa-check"></i>
                            {{ __('Inisialisasi Risk Plan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
