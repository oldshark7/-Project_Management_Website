<div class="p-16 text-center">
    <div
        class="w-12 h-12 bg-slate-50 text-slate-400 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm">
        <i class="fas fa-users text-xl"></i>
    </div>
    @if ($isEditable)
        <h5 class="font-bold text-sm text-slate-800 mb-1">
            {{ __('Alokasi SDM Kosong') }}
        </h5>
        <p class="text-xs text-slate-500 mb-4">
            {{ __('Belum ada rincian alokasi kebutuhan tim pelaksana untuk proyek ini.') }}
        </p>
        <button type="button" onclick="openAddModal()"
            class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-800 border border-slate-200 rounded-xl text-xs font-bold hover:bg-slate-250 transition gap-1.5 shadow-sm">
            <i class="fas fa-plus"></i>
            {{ __('Tambahkan Tim Pertama') }}
        </button>
    @else
        <h5 class="font-bold text-sm text-slate-800 mb-1">
            {{ __('Alokasi SDM Kosong') }}
        </h5>
        <p class="text-xs text-slate-500 mb-4">
            {{ __('Belum ada rincian alokasi kebutuhan tim pelaksana untuk proyek ini.') }}
        </p>
    @endif

</div>
