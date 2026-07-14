<x-app-layout>
    <x-slot name="header">
        <x-header-component :title="'Scope Management'" icon="fa-solid fa-sitemap text-blue-600 text-lg" />
    </x-slot>

    <div class="px-4 py-2">
        <!-- Header Section -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight mb-1">{{ __('Cakupan Proyek (Scope Management)') }}</h2>
                <p class="text-xs text-slate-500">
                    @if(strtolower(Auth::user()->role) === 'manager')
                        {{ __('Buat, kelola, dan finalisasi ruang lingkup proyek untuk acuan WBS.') }}
                    @else
                        {{ __('Pantau dan lihat detail ruang lingkup proyek yang sedang berjalan.') }}
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('project-planning') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 hover:text-slate-800 font-bold rounded-xl text-xs transition shadow-sm gap-2">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    {{ __('Kembali ke Planning') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                <span class="font-semibold">{{ session('info') }}</span>
            </div>
        @endif

        <!-- List Projects in Planning Status -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            @if($projects->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i class="fas fa-sitemap text-2xl"></i>
                    </div>
                    <h4 class="font-extrabold text-lg text-slate-800 mb-1">{{ __('Tidak ada proyek ditemukan') }}</h4>
                    <p class="text-xs text-slate-500">{{ __('Belum ada proyek dalam status Planning yang tersedia untuk Anda.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-4">{{ __('Nama Proyek') }}</th>
                                <th class="px-6 py-4">{{ __('Project Manager') }}</th>
                                <th class="px-6 py-4">{{ __('Status Project') }}</th>
                                <th class="px-6 py-4">{{ __('Status Scope') }}</th>
                                <th class="px-6 py-4 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            @foreach($projects as $project)
                                @php
                                    $userRole = strtolower(Auth::user()->role);
                                @endphp
                                <tr class="hover:bg-slate-50/30 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 text-sm mb-0.5">{{ $project->title }}</div>
                                        <div class="text-[10px] text-slate-400">{{ __('Mulai: ') . ($project->start_date ? $project->start_date->format('d M Y') : '-') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 font-semibold">
                                        {{ $project->owner ? $project->owner->name : '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            {{ __('Planning') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($project->scope)
                                            @php
                                                $scopeStatusClasses = [
                                                    'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
                                                    'finalized' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                ][$project->scope->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-lg text-[10px] font-bold border {{ $scopeStatusClasses }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                {{ $project->scope->status === 'finalized' ? __('Finalized') : ucfirst($project->scope->status) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                {{ __('Belum Dibuat') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex gap-2">
                                            @if($project->scope)
                                                <a href="{{ route('projects.scope.show', $project->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition gap-1.5">
                                                    <i class="fas fa-eye text-slate-400"></i> {{ __('Detail Scope') }}
                                                </a>
                                                @if($project->scope->status === 'draft' && $userRole === 'manager')
                                                    <a href="{{ route('projects.scope.edit', $project->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 shadow-sm transition gap-1.5">
                                                        <i class="fas fa-edit text-amber-500"></i> {{ __('Edit') }}
                                                    </a>
                                                @endif
                                            @else
                                                @if($userRole === 'manager')
                                                    <a href="{{ route('projects.scope.create', $project->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 shadow-sm transition gap-1.5">
                                                        <i class="fas fa-plus text-blue-500"></i> {{ __('Buat Scope') }}
                                                    </a>
                                                @else
                                                    <span class="text-xs text-slate-400 italic font-semibold py-1.5 px-3 block">
                                                        {{ __('Belum dibuat oleh Manager') }}
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
