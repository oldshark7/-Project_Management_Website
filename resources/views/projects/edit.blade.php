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
                    {{ __('Ubah Proyek') }}
                </h2>
                <h3 class="text-sm text-secondaryText mt-1">
                    @if(strtolower(Auth::user()->role) === 'project manager')
                        {{ __('Perbarui informasi proyek Anda atau ajukan proyek ini untuk ditinjau.') }}
                    @else
                        {{ __('Tinjau detail proyek dan ubah status persetujuan.') }}
                    @endif
                </h3>
            </div>

            <!-- Validation/Status Transition Error Alert -->
            @if($errors->has('status'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-rose-500"></i>
                    <span>{{ $errors->first('status') }}</span>
                </div>
            @endif

            <!-- Form Card -->
            <div class="bg-white p-6 rounded-xl border border-[#e3e3e0] shadow-sm">
                <form action="{{ route('projects.update', $project->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if(strtolower(Auth::user()->role) === 'project manager')
                        <!-- EDIT MODE FOR PROJECT MANAGER -->

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Judul Proyek') }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title', $project->title) }}" 
                                   class="w-full px-4 py-2 border border-[#e3e3e0] rounded-xl text-sm text-primaryText focus:ring-primary focus:border-primary transition" 
                                   required>
                            @error('title')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Deskripsi Proyek') }}</label>
                            <textarea name="description" id="description" rows="4" 
                                      class="w-full px-4 py-2 border border-[#e3e3e0] rounded-xl text-sm text-primaryText focus:ring-primary focus:border-primary transition">{{ old('description', $project->description) }}</textarea>
                            @error('description')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Tanggal Mulai') }}</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}" 
                                       class="w-full px-4 py-2 border border-[#e3e3e0] rounded-xl text-sm text-primaryText focus:ring-primary focus:border-primary transition">
                                @error('start_date')
                                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Tanggal Selesai') }}</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $project->end_date ? $project->end_date->format('Y-m-d') : '') }}" 
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
                                    <option value="{{ $manager->id }}" {{ old('manager_id', $project->manager_id) == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }} ({{ $manager->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('manager_id')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status display (disabled edit) -->
                        <div class="mb-6 p-4 bg-gray-50 border border-[#e3e3e0] rounded-xl">
                            <div class="text-xs font-semibold text-secondaryText uppercase tracking-wider mb-1">{{ __('Status Saat Ini') }}</div>
                            <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                {{ ucfirst($project->status) }}
                            </span>
                        </div>

                        <!-- Actions for PM -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('projects.index') }}" class="px-4 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 rounded-xl text-sm font-semibold shadow-sm transition duration-200">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" name="action" value="save" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-sm font-semibold transition duration-200">
                                {{ __('Simpan Perubahan') }}
                            </button>
                            <button type="submit" name="action" value="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-md hover:shadow-lg shadow-blue-500/10 transition duration-200">
                                {{ __('Ajukan Proyek (Submit)') }}
                            </button>
                        </div>

                    @elseif(strtolower(Auth::user()->role) === 'manager')
                        <!-- STATUS REVIEW MODE FOR MANAGER -->

                        <!-- Read-only Details -->
                        <div class="space-y-4 mb-6">
                            <div>
                                <h4 class="text-xs font-semibold text-secondaryText uppercase tracking-wider mb-1">{{ __('Judul Proyek') }}</h4>
                                <p class="text-base font-semibold text-primaryText">{{ $project->title }}</p>
                            </div>

                            @if($project->description)
                                <div>
                                    <h4 class="text-xs font-semibold text-secondaryText uppercase tracking-wider mb-1">{{ __('Deskripsi') }}</h4>
                                    <p class="text-sm text-primaryText whitespace-pre-line">{{ $project->description }}</p>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xs font-semibold text-secondaryText uppercase tracking-wider mb-1">{{ __('Pembuat (Owner)') }}</h4>
                                    <p class="text-sm text-primaryText">{{ $project->owner ? $project->owner->name : '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-secondaryText uppercase tracking-wider mb-1">{{ __('Rentang Tanggal') }}</h4>
                                    <p class="text-sm text-primaryText">
                                        @if($project->start_date && $project->end_date)
                                            {{ $project->start_date->format('d M Y') }} - {{ $project->end_date->format('d M Y') }}
                                        @elseif($project->start_date)
                                            {{ __('Mulai: ') . $project->start_date->format('d M Y') }}
                                        @else
                                            <span class="text-gray-400 italic">{{ __('Belum diatur') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Status Selection (Manager Controls) -->
                        <div class="mb-6 p-4 bg-gray-50 border border-[#e3e3e0] rounded-xl">
                            <label for="status" class="block text-sm font-semibold text-primaryText mb-1.5">{{ __('Ubah Status Proyek') }}</label>
                            
                            <select name="status" id="status" 
                                    class="w-full px-4 py-2 border border-[#e3e3e0] rounded-xl text-sm text-primaryText focus:ring-primary focus:border-primary transition" required>
                                <option value="{{ $project->status }}" selected>{{ __('Tetap: ') . ucfirst($project->status) }}</option>
                                
                                @if($project->status === 'submitted')
                                    <option value="approved">{{ __('Approve (Setujui)') }}</option>
                                    <option value="rejected">{{ __('Reject (Tolak)') }}</option>
                                @elseif($project->status === 'approved')
                                    <option value="planning">{{ __('Move to Planning (Pindahkan ke Perencanaan)') }}</option>
                                @endif
                            </select>

                            <p class="text-xs text-secondaryText mt-2">
                                @if($project->status === 'submitted')
                                    <i class="fas fa-info-circle mr-1"></i> {{ __('Manager dapat menyetujui (approve) atau menolak (reject) proyek yang diajukan.') }}
                                @elseif($project->status === 'approved')
                                    <i class="fas fa-info-circle mr-1"></i> {{ __('Manager dapat memindahkan proyek yang disetujui ke fase perencanaan (planning).') }}
                                @else
                                    <i class="fas fa-info-circle mr-1"></i> {{ __('Status proyek saat ini tidak memerlukan persetujuan Manager.') }}
                                @endif
                            </p>
                        </div>

                        <!-- Actions for Manager -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('projects.index') }}" class="px-4 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 rounded-xl text-sm font-semibold shadow-sm transition duration-200">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-md hover:shadow-lg shadow-blue-500/10 transition duration-200">
                                {{ __('Perbarui Status') }}
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
