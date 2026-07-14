<x-app-layout>
    <div class="px-4 py-2">
        <!-- Top Bar / Header Redesign -->
        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
            <!-- Left: Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('projects.wbs.show', $project->id) }}" class="text-slate-400 hover:text-slate-600 transition font-medium">WBS Proyek</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-bold">Tambah Item WBS</span>
            </div>

            <!-- Right: Actions & User Info -->
            <div class="flex items-center gap-4 justify-end shrink-0 w-full sm:w-auto">
                <div class="hidden sm:block border-l border-slate-200 h-8"></div>
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

        <div class="max-w-3xl mx-auto">
            <!-- Back Navigation button -->
            <div class="mb-6">
                <a href="{{ route('projects.wbs.show', $project->id) }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition gap-1.5">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    {{ __('Kembali ke WBS Proyek') }}
                </a>
            </div>

            <!-- Page Header -->
            <div class="mb-6">
                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">
                    PROYEK: {{ strtoupper($project->title) }}
                </p>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">{{ __('Tambah Item WBS Baru') }}</h2>
                <p class="text-xs text-slate-500 mt-1">Pecah ruang lingkup pekerjaan menjadi tugas-tugas detail di bawah ini.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mb-12">
                <form action="{{ route('projects.wbs.store', $project->id) }}" method="POST" id="wbsForm" class="space-y-6">
                    @csrf

                    <!-- WBS Title -->
                    <div>
                        <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Judul / Nama Tugas *') }}</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}"
                               class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150 placeholder-slate-400" 
                               placeholder="Tuliskan judul tugas atau bagian kerja..." required>
                        @error('title')
                            <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- WBS Description -->
                    <div>
                        <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Deskripsi Pekerjaan *') }}</label>
                        <textarea name="description" id="description" rows="4" 
                                  class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150 placeholder-slate-400" 
                                  placeholder="Jelaskan secara detail apa yang akan dikerjakan pada tugas ini..." required>{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deliverable -->
                    <div>
                        <label for="deliverable" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Hasil Kerja / Deliverable (Optional)') }}</label>
                        <input type="text" name="deliverable" id="deliverable" value="{{ old('deliverable') }}"
                               class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150 placeholder-slate-400" 
                               placeholder="Hasil akhir fisik atau dokumen dari tugas ini (misal: dokumen SRS, modul login, database schema)...">
                        @error('deliverable')
                            <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Prioritas Tugas *') }}</label>
                            <select name="priority" id="priority" 
                                    class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150">
                                <option value="low" {{ old('priority', 'medium') === 'low' ? 'selected' : '' }}>{{ __('Low (Rendah)') }}</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>{{ __('Medium (Sedang)') }}</option>
                                <option value="high" {{ old('priority', 'medium') === 'high' ? 'selected' : '' }}>{{ __('High (Tinggi)') }}</option>
                            </select>
                            @error('priority')
                                <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estimated Duration -->
                        <div>
                            <label for="estimated_duration_days" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Estimasi Durasi (Hari) (Optional)') }}</label>
                            <input type="number" name="estimated_duration_days" id="estimated_duration_days" min="1" value="{{ old('estimated_duration_days') }}"
                                   class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150 placeholder-slate-400" 
                                   placeholder="Jumlah hari yang dibutuhkan (misal: 5)">
                            @error('estimated_duration_days')
                                <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Parent WBS Item (Hierarchy) -->
                    <div>
                        <label for="parent_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Parent Tugas / Sub-Task Dari (Optional)') }}</label>
                        <select name="parent_id" id="parent_id" 
                                class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150">
                            <option value="">-- {{ __('Tugas Utama / Root Task (Tanpa Parent)') }} --</option>
                            @foreach($parentItems as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', request('parent_id')) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->title }} (ID: #{{ $parent->id }})
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('projects.wbs.show', $project->id) }}" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 hover:text-slate-800 rounded-xl text-xs font-bold shadow-sm transition duration-150">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition duration-150">
                            {{ __('Tambah Item') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
