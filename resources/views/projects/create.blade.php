<x-app-layout>
    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    <div class="pl-4 pt-4">
        <div class="bg-cardSection rounded-xl p-6 max-w-3xl mx-auto">
            <!-- Header Section -->
            <div class="mb-6">
                <a href="{{ route('projects.index') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-gray-800 mb-2 transition gap-1">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('Kembali ke Daftar') }}
                </a>
                <h2 class="font-semibold text-2xl text-primaryText leading-tight">
                    {{ __('Buat Proyek Baru') }}
                </h2>
                <h3 class="text-sm text-secondaryText mt-1">
                    {{ __('Isi detail informasi proyek di bawah ini untuk membuat draf baru.') }}
                </h3>
            </div>

            <!-- Form Card -->
            <div class="bg-white p-6 rounded-xl border border-[#e3e3e0] shadow-sm">
                <form action="{{ route('projects.store') }}" method="POST">
                    @csrf

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Judul Proyek') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" 
                               class="w-full px-4 py-2 border border-[#e3e3e0] rounded-xl text-sm text-primaryText focus:ring-primary focus:border-primary transition" 
                               placeholder="Contoh: Implementasi Sistem Manajemen Inventaris Baru" required>
                        @error('title')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Deskripsi Proyek') }}</label>
                        <textarea name="description" id="description" rows="4" 
                                  class="w-full px-4 py-2 border border-[#e3e3e0] rounded-xl text-sm text-primaryText focus:ring-primary focus:border-primary transition" 
                                  placeholder="Jelaskan latar belakang, tujuan, dan ruang lingkup awal proyek..."></textarea>
                        @error('description')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Tanggal Mulai') }}</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" 
                                   class="w-full px-4 py-2 border border-[#e3e3e0] rounded-xl text-sm text-primaryText focus:ring-primary focus:border-primary transition">
                            @error('start_date')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Tanggal Selesai') }}</label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" 
                                   class="w-full px-4 py-2 border border-[#e3e3e0] rounded-xl text-sm text-primaryText focus:ring-primary focus:border-primary transition">
                            @error('end_date')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Assigned Manager -->
                    <div class="mb-6">
                        <label for="manager_id" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Tunjuk Manager Pendamping') }}</label>
                        <select name="manager_id" id="manager_id" 
                                class="w-full px-4 py-2 border border-[#e3e3e0] rounded-xl text-sm text-primaryText focus:ring-primary focus:border-primary transition">
                            <option value="">{{ __('Pilih Manager (Opsional)') }}</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} ({{ $manager->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('manager_id')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('projects.index') }}" class="px-4 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 rounded-xl text-sm font-semibold shadow-sm transition duration-200">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-md hover:shadow-lg shadow-blue-500/10 transition duration-200">
                            {{ __('Simpan Draft') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
