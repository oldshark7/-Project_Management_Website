<x-app-layout>
    <div class="px-4 py-2">
        <!-- Top Bar / Header Redesign -->
        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
            <!-- Left: Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('projects.timeline.show', $project->id) }}" class="text-slate-400 hover:text-slate-600 transition font-medium">Timeline Proyek</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-bold">Jadwalkan Tugas</span>
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
                <a href="{{ route('projects.timeline.show', $project->id) }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition gap-1.5">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    {{ __('Kembali ke Timeline Proyek') }}
                </a>
            </div>

            <!-- Page Header -->
            <div class="mb-6">
                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">
                    PROYEK: {{ strtoupper($project->title) }}
                </p>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">{{ __('Tambah Jadwal Timeline Baru') }}</h2>
                <p class="text-xs text-slate-500 mt-1">Pilih tugas dari WBS dan tentukan tanggal pelaksanaan kerjanya.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mb-12">
                <form action="{{ route('projects.timeline.store', $project->id) }}" method="POST" id="timelineForm" class="space-y-6">
                    @csrf

                    <!-- WBS Item Selection -->
                    <div>
                        <label for="wbs_item_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Pilih Item WBS *') }}</label>
                        <select name="wbs_item_id" id="wbs_item_id" 
                                class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150">
                            <option value="">-- {{ __('Pilih Item WBS yang akan dijadwalkan') }} --</option>
                            @foreach($wbsItems as $wbs)
                                <option value="{{ $wbs->id }}" {{ old('wbs_item_id', request('wbs_id')) == $wbs->id ? 'selected' : '' }}>
                                    {{ $wbs->title }} (ID: #{{ $wbs->id }})
                                </option>
                            @endforeach
                        </select>
                        @error('wbs_item_id')
                            <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date range grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Tanggal Mulai *') }}</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                                   class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150" required>
                            @error('start_date')
                                <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Tanggal Selesai *') }}</label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                                   class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150" required>
                            @error('end_date')
                                <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Predecessor/Dependency Selection -->
                    <div>
                        <label for="dependency_wbs_item_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Tugas Prasyarat / Predecessor (Optional)') }}</label>
                        <select name="dependency_wbs_item_id" id="dependency_wbs_item_id" 
                                class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150">
                            <option value="">-- {{ __('Pilih tugas yang harus selesai sebelum tugas ini dimulai') }} --</option>
                            @foreach($dependencyItems as $dep)
                                <option value="{{ $dep->id }}" {{ old('dependency_wbs_item_id') == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->title }} (Selesai: {{ $dep->timelineItem ? $dep->timelineItem->end_date->format('d-m-Y') : '-' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 font-bold mt-1.5"><i class="fas fa-info-circle mr-1"></i>{{ __('Catatan: Tanggal mulai tugas ini tidak boleh lebih cepat dari tanggal selesai tugas prasyarat.') }}</p>
                        @error('dependency_wbs_item_id')
                            <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Milestone Trigger -->
                    <div>
                        <label for="is_milestone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Apakah ini Milestone? *') }}</label>
                        <select name="is_milestone" id="is_milestone" 
                                class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150">
                            <option value="0" {{ old('is_milestone', '0') === '0' ? 'selected' : '' }}>{{ __('Bukan Milestone') }}</option>
                            <option value="1" {{ old('is_milestone', '0') === '1' ? 'selected' : '' }}>{{ __('Ya, ini Milestone') }}</option>
                        </select>
                        @error('is_milestone')
                            <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Milestone Name (Conditionally Displayed) -->
                    <div id="milestone_name_group" class="{{ old('is_milestone', '0') === '1' ? '' : 'hidden' }}">
                        <label for="milestone_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Nama Milestone *') }}</label>
                        <input type="text" name="milestone_name" id="milestone_name" value="{{ old('milestone_name') }}"
                               class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150 placeholder-slate-400" 
                               placeholder="Tuliskan nama pencapaian utama/milestone (misal: Rilis Alpha, UAT Selesai)...">
                        @error('milestone_name')
                            <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ __('Catatan Tambahan (Optional)') }}</label>
                        <textarea name="notes" id="notes" rows="3" 
                                  class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition duration-150 placeholder-slate-400" 
                                  placeholder="Tambahkan catatan khusus terkait jadwal ini...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-rose-500 text-[10px] font-bold mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('projects.timeline.show', $project->id) }}" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 hover:text-slate-800 rounded-xl text-xs font-bold shadow-sm transition duration-150">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-[#0B1329] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition duration-150">
                            {{ __('Simpan Jadwal') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toggle Milestone Name Script & Predecessor dynamic map -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isMilestoneSelect = document.getElementById('is_milestone');
            const milestoneGroup = document.getElementById('milestone_name_group');

            if (isMilestoneSelect && milestoneGroup) {
                isMilestoneSelect.addEventListener('change', function () {
                    if (this.value === '1') {
                        milestoneGroup.classList.remove('hidden');
                    } else {
                        milestoneGroup.classList.add('hidden');
                        const nameInput = document.getElementById('milestone_name');
                        if (nameInput) nameInput.value = '';
                    }
                });
            }

            const wbsSelect = document.getElementById('wbs_item_id');
            const depSelect = document.getElementById('dependency_wbs_item_id');
            const invalidMap = @json($invalidPredecessorsMap);

            function updatePredecessors() {
                if (!wbsSelect || !depSelect) return;
                const selectedWbsId = wbsSelect.value;
                const invalidIds = invalidMap[selectedWbsId] || [];

                Array.from(depSelect.options).forEach(option => {
                    if (option.value === "") {
                        option.disabled = false;
                        option.style.display = "";
                        return;
                    }

                    const optValue = parseInt(option.value);
                    if (invalidIds.includes(optValue)) {
                        option.disabled = true;
                        option.style.display = "none";
                        if (option.selected) {
                            depSelect.value = "";
                        }
                    } else {
                        option.disabled = false;
                        option.style.display = "";
                    }
                });
            }

            if (wbsSelect && depSelect) {
                wbsSelect.addEventListener('change', updatePredecessors);
                updatePredecessors();
            }
        });
    </script>
</x-app-layout>
