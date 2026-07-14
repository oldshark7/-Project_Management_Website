<x-app-layout>
    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    <div class="pl-4 pt-4 pb-12">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('PERENCANAAN PROYEK') }} / {{ __('RENCANA ANGGARAN BIAYA (RAB)') }}</div>
                    <h2 class="font-extrabold text-2xl text-slate-800 leading-tight mt-1 flex items-center gap-2">
                        <i class="fa-solid fa-wallet text-slate-700 text-xl"></i>
                        {{ __('Perencanaan Anggaran (RAB)') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        @if(strtolower(Auth::user()->role) === 'manager')
                            {{ __('Kelola dan susun alokasi dana proyek secara presisi berdasarkan baseline anggaran awal.') }}
                        @else
                            {{ __('Tinjau rincian rencana anggaran belanja (RAB) dan alokasi biaya proyek.') }}
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ route('project-planning') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 hover:text-slate-900 font-bold rounded-xl text-xs transition shadow-sm gap-2">
                        <i class="fas fa-arrow-left text-[9px]"></i>
                        {{ __('Kembali ke Perencanaan') }}
                    </a>
                </div>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl text-xs flex items-center gap-2.5 shadow-sm">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-xs flex items-center gap-2.5 shadow-sm">
                    <i class="fas fa-exclamation-circle text-rose-500"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 bg-blue-50 border border-blue-100 text-blue-800 rounded-xl text-xs flex items-center gap-2.5 shadow-sm">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
            @endif

            <!-- List Projects Table (Redesigned) -->
            <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
                @if($projects->isEmpty())
                    <div class="p-16 text-center">
                        <div class="w-16 h-16 bg-slate-50 text-slate-400 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-wallet text-2xl"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-800 mb-1 text-base">{{ __('Tidak Ada Proyek') }}</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">{{ __('Belum ada proyek dalam status Planning yang aktif untuk penyusunan anggaran belanja.') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="px-6 py-4">{{ __('NAMA PROYEK') }}</th>
                                    <th class="px-6 py-4">{{ __('PROJECT MANAGER') }}</th>
                                    <th class="px-6 py-4">{{ __('STATUS TIMELINE') }}</th>
                                    <th class="px-6 py-4">{{ __('STATUS BUDGET') }}</th>
                                    <th class="px-6 py-4 text-right">{{ __('AKSI') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-xs">
                                @foreach($projects as $project)
                                    @php
                                        $userRole = strtolower(Auth::user()->role);
                                        
                                        // Check Timeline finalization status
                                        $wbsCount = $project->wbsItems->count();
                                        $timelineCount = $project->timelineItems->count();
                                        $timelineDraftCount = $project->timelineItems->where('status', 'draft')->count();
                                        $isTimelineFinalized = ($timelineCount > 0 && $timelineDraftCount === 0 && $timelineCount === $wbsCount);
                                        
                                        // Budget plan status
                                        $budgetPlan = $project->budgetPlan;
                                        $budgetStatus = $budgetPlan ? $budgetPlan->status : 'none';
                                    @endphp
                                    <tr class="hover:bg-slate-50/30 transition duration-150">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800 text-sm hover:text-blue-600 transition duration-150">
                                                {{ $project->title }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-semibold mt-1 flex items-center gap-1.5">
                                                <i class="far fa-calendar-alt text-slate-300"></i>
                                                {{ __('Mulai: ') . ($project->start_date ? $project->start_date->format('d M Y') : '-') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 font-bold">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-[#0B1329] text-white flex items-center justify-center font-black text-[9px] shadow-sm uppercase tracking-wider">
                                                    {{ strtoupper(substr($project->owner ? $project->owner->name : 'PM', 0, 2)) }}
                                                </div>
                                                <span class="text-slate-700 font-bold">{{ $project->owner ? $project->owner->name : '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($isTimelineFinalized)
                                                <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-lg text-[10px] font-extrabold border bg-emerald-50 text-emerald-600 border-emerald-100">
                                                    <i class="fas fa-check-circle text-[9px]"></i>
                                                    {{ __('Finalized') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-lg text-[10px] font-extrabold border bg-rose-50 text-rose-600 border-rose-100">
                                                    <i class="fas fa-exclamation-circle text-[9px]"></i>
                                                    {{ __('Belum Final') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if(!$isTimelineFinalized)
                                                <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-lg text-[10px] font-extrabold border bg-slate-50 text-slate-500 border-slate-200">
                                                    <i class="fas fa-clock text-[8px]"></i>
                                                    {{ __('Menunggu Timeline') }}
                                                </span>
                                            @elseif($budgetStatus === 'none')
                                                <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-lg text-[10px] font-extrabold border bg-rose-50 text-rose-600 border-rose-100">
                                                    <i class="fas fa-ban text-[8px]"></i>
                                                    {{ __('Belum Dibuat') }}
                                                </span>
                                            @elseif($budgetStatus === 'draft')
                                                <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-lg text-[10px] font-extrabold border bg-amber-50 text-amber-600 border-amber-100">
                                                    <i class="fas fa-file-signature text-[8px]"></i>
                                                    {{ __('Draft') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-lg text-[10px] font-extrabold border bg-emerald-50 text-emerald-600 border-emerald-100">
                                                    <i class="fas fa-check-double text-[8px]"></i>
                                                    {{ __('Finalized') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="inline-flex gap-2">
                                                @if($isTimelineFinalized)
                                                    @if($budgetStatus === 'none')
                                                        @if($userRole === 'manager')
                                                            <a href="{{ route('projects.budget.create', $project->id) }}" class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-bold text-white bg-[#0B1329] hover:bg-slate-800 rounded-xl shadow-sm transition gap-1.5">
                                                                <i class="fas fa-plus text-[9px]"></i> {{ __('Buat Budget') }}
                                                            </a>
                                                        @else
                                                            <span class="text-xs text-slate-400 italic font-semibold py-2 px-3 block">
                                                                {{ __('Belum dibuat') }}
                                                            </span>
                                                        @endif
                                                    @elseif($budgetStatus === 'draft')
                                                        @if($userRole === 'manager')
                                                            <a href="{{ route('projects.budget.edit', $project->id) }}" class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-sm transition gap-1.5">
                                                                <i class="fas fa-edit text-[9px]"></i> {{ __('Kelola Budget') }}
                                                            </a>
                                                        @else
                                                            <a href="{{ route('projects.budget.show', $project->id) }}" class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl shadow-sm transition gap-1.5">
                                                                <i class="fas fa-eye text-[9px] text-slate-400"></i> {{ __('Detail Budget') }}
                                                            </a>
                                                        @endif
                                                    @else
                                                        <a href="{{ route('projects.budget.show', $project->id) }}" class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl shadow-sm transition gap-1.5">
                                                            <i class="fas fa-eye text-[9px] text-slate-400"></i> {{ __('Detail Budget') }}
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-slate-400 italic font-semibold py-2 px-3 block flex items-center gap-1">
                                                        <i class="fas fa-lock text-[9px]"></i> {{ __('Timeline Belum Siap') }}
                                                    </span>
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
    </div>
</x-app-layout>
