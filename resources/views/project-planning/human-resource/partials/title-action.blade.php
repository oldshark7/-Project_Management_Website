<div class="border-b border-slate-100 pb-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

    <div>
        <h4 class="card-title text-black">
            {{ __('Daftar Alokasi Sumber Daya') }}
        </h4>

        <p class="text-[11px] text-slate-400 font-medium mt-0.5">
            {{ $isEditable
                ? __('Kebutuhan peran, kompetensi, PIC, alokasi beban kerja, dan aksi.')
                : __('Kebutuhan peran, kompetensi, PIC, dan alokasi beban kerja.') }}
        </p>
    </div>

    <div class="flex items-center gap-2 shrink-0">

        @if ($isEditable)
            <button type="button" onclick="openAddModal()"
                class="inline-flex items-center justify-center px-4 py-2 bg-[#0B1329] hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm transition gap-1.5">
                <i class="fas fa-plus text-[9px]"></i>
                {{ __('Tambah Peran') }}
            </button>

            <button type="button"
                class="p-1.5 border border-slate-200 rounded-lg text-slate-400 hover:text-slate-650 hover:bg-slate-50 text-xs transition">
                <i class="fas fa-filter"></i>
            </button>

            <button type="button"
                class="p-1.5 border border-slate-200 rounded-lg text-slate-400 hover:text-slate-655 hover:bg-slate-50 text-xs transition">
                <i class="fas fa-download"></i>
            </button>


        @else
            <button type="button"
                class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-lg text-xs transition gap-1.5 shadow-sm">
                <i class="fas fa-filter text-slate-400"></i>
                {{ __('Filter') }}
            </button>

            <button type="button"
                class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-lg text-xs transition gap-1.5 shadow-sm">
                <i class="fas fa-download text-slate-400"></i>
                {{ __('Ekspor') }}
            </button>
        @endif

    </div>
</div>
