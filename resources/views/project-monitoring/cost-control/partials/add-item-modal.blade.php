<div id="add-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeAddModal()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
            <form action="{{ route('projects.budget.items.store', $project->id) }}" method="POST">
                @csrf
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-plus text-slate-900"></i>
                            {{ __('Tambah Item Anggaran') }}
                        </h3>
                        <button type="button" onclick="closeAddModal()"
                            class="text-slate-400 hover:text-slate-600 transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Kategori -->
                        <div>
                            <label for="add_category"
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Kategori') }}</label>
                            <select name="category" id="add_category" required
                                class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100">
                                <option value="">-- {{ __('Pilih Kategori') }} --</option>
                                @foreach ($categories as $key => $cat)
                                    <option value="{{ $key }}"
                                        {{ old('category') === $key ? 'selected' : '' }}>{{ $cat['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label for="add_description"
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Deskripsi Pekerjaan / Kebutuhan') }}</label>
                            <input type="text" name="description" id="add_description" required
                                value="{{ old('description') }}" placeholder="Contoh: Honor Senior System Analyst"
                                class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-semibold text-slate-700">
                        </div>

                        <!-- Quantity & Unit -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="add_quantity"
                                    class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Quantity') }}</label>
                                <input type="number" name="quantity" id="add_quantity" required min="1"
                                    value="{{ old('quantity', 1) }}"
                                    class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-bold text-slate-700">
                            </div>
                            <div>
                                <label for="add_unit"
                                    class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Satuan (Unit)') }}</label>
                                <input type="text" name="unit" id="add_unit" required
                                    value="{{ old('unit', 'Bulan') }}" placeholder="Contoh: Orang, Unit"
                                    class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-semibold text-slate-700">
                            </div>
                        </div>

                        <!-- Unit Cost -->
                        <div>
                            <label for="add_unit_cost"
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Harga Satuan (Rp)') }}</label>
                            <input type="number" name="unit_cost" id="add_unit_cost" required min="0"
                                value="{{ old('unit_cost') }}" placeholder="Contoh: 5000000"
                                class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 font-mono font-bold text-slate-700">
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label for="add_notes"
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Catatan Khusus (Opsional)') }}</label>
                            <textarea name="notes" id="add_notes" rows="2"
                                class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100 text-slate-700"
                                placeholder="Keterangan tambahan biaya... ">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-150 rounded-xl text-xs font-bold transition">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm">
                        {{ __('Simpan Item') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
