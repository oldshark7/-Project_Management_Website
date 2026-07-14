<x-app-layout>
    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    <div class="pl-4 pt-4">
        <div class="bg-cardSection rounded-xl p-6">
            <!-- Title section -->
            <div class="mb-4">
                <h2 class="font-semibold text-2xl text-primaryText leading-tight">
                    {{ __('Project Initiation') }}
                </h2>
                <h3 class="text-sm text-secondaryText mt-1">
                    {{ __('Kelola draf proposal, project charter, dan periksa checklist kelengkapan dokumen inisiasi Anda.') }}
                </h3>
            </div>

            <!-- Content Area Placeholder -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <!-- Proposal Card -->
                <div class="bg-white p-6 rounded-xl border border-[#e3e3e0] shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center text-primary">
                            <i class="fas fa-file-alt text-lg"></i>
                        </div>
                        <h4 class="font-semibold text-lg text-primaryText">{{ __('Project Proposal') }}</h4>
                    </div>
                    <p class="text-sm text-secondaryText mb-4">{{ __('Buat, draf, dan ajukan proposal proyek Anda kepada Manager untuk disetujui.') }}</p>
                    <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                        {{ __('Under Development') }}
                    </span>
                </div>

                <!-- Charter Card -->
                <div class="bg-white p-6 rounded-xl border border-[#e3e3e0] shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center text-primary">
                            <i class="fas fa-file-contract text-lg"></i>
                        </div>
                        <h4 class="font-semibold text-lg text-primaryText">{{ __('Project Charter') }}</h4>
                    </div>
                    <p class="text-sm text-secondaryText mb-4">{{ __('Formalkan proyek Anda dengan mendefinisikan ruang lingkup, milestone, dan alokasi dana awal.') }}</p>
                    <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                        {{ __('Under Development') }}
                    </span>
                </div>

                <!-- Checklist Card -->
                <div class="bg-white p-6 rounded-xl border border-[#e3e3e0] shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center text-primary">
                            <i class="fas fa-clipboard-list text-lg"></i>
                        </div>
                        <h4 class="font-semibold text-lg text-primaryText">{{ __('Checklist Kelengkapan') }}</h4>
                    </div>
                    <p class="text-sm text-secondaryText mb-4">{{ __('Periksa kelengkapan berkas inisiasi sebelum melangkah ke tahap Project Planning.') }}</p>
                    <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                        {{ __('Under Development') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
