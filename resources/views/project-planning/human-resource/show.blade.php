<x-app-layout>
    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    @php
        $tab = request('tab', 'hr');
    @endphp
    
    <div class="p-6 bg-white rounded-2xl border-slate-100 border shadow-sm">
        <div class="w-full mx-auto space-y-6">


            <!-- Back Navigation & Header Section -->
            <div class="space-y-4">
                @include('project-planning.human-resource.partials.sub-header', [
                    'breadcrumb' => __('PLANNING') . ' / ' . __('HUMAN RESOURCE') . ' / ' . __('SHOW'),
                    'title' => __('Alokasi & Kapasitas Tim'),
                    'description' => __(
                        'Manage personnel workloads and allocate strategic roles to ensure the timely success of the project.'),
                    'project' => $project,
                    'actionButtonEnabled' => $isPmo && $isDraft,
                ])
            </div>

            <!-- Top Sub-Navigation Tabs (Redesigned as sleek pill tabs) -->
            @include('project-planning.human-resource.partials.sub-navigation')

            <!-- Alerts -->
            @if (session('success'))
                <div
                    class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl text-xs flex items-center gap-2.5 shadow-sm">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('info'))
                <div
                    class="p-4 bg-blue-50 border border-blue-100 text-blue-800 rounded-xl text-xs flex items-center gap-2.5 shadow-sm">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
            @endif

            <!-- Finalized / Draft Banner -->
            {{-- @include('project-planning.human-resource.partials.finalized-draf-banner') --}}

            @if ($tab === 'hr')
                <!-- Plan content -->
                @if (!$hrPlan)
                    @include('project-planning.human-resource.partials.draf-message')
                @else
                    <!-- Metric Cards Row (Span full width) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @include('project-planning.human-resource.partials.total-personil-card')
                        @include('project-planning.human-resource.partials.avarage-weight-card')
                        @include('project-planning.human-resource.partials.project-quality-card')
                    </div>

                    <!-- Main Resource List Table Card -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        @include('project-planning.human-resource.partials.title-action', [
                            'isEditable' => false,
                        ])

                        <!-- Table -->
                        @if ($hrItems->isEmpty())
                            @include('project-planning.human-resource.partials.empty-message', [
                                'isEditable' => false,
                            ])
                        @else
                            <div class="overflow-x-auto">
                                @include('project-planning.human-resource.partials.workload-table', ['isEditable' => true])
                            </div>

                            <!-- Footer Pagination / Stats -->
                            <div
                                class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500 font-medium">
                                <div id="pagination-stats">
                                    {{ __('Menampilkan ') }}
                                    <span class="font-bold text-slate-700" id="visible-count">{{ $hrItems->unique('team_member_id')->count() }} </span>
                                    {{ __(' dari ') }}
                                    <span class="font-bold text-slate-700">{{ $hrItems->unique('team_member_id')->count() }} </span>
                                    {{ __(' personil') }}
                                </div>
                                <div class="inline-flex gap-1">
                                    <button type="button" disabled
                                        class="px-3 py-1 border border-slate-200 rounded-lg text-slate-400 bg-slate-50 cursor-not-allowed text-[11px] font-bold">Sebelumnya</button>
                                    <button type="button"
                                        class="px-3 py-1 border border-slate-800 bg-slate-800 text-white rounded-lg text-[11px] font-bold">1</button>
                                    <button type="button" disabled
                                        class="px-3 py-1 border border-slate-200 rounded-lg text-slate-400 bg-slate-50 cursor-not-allowed text-[11px] font-bold">Selanjutnya</button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Notes Card (Displaying Notes elegantly at the bottom) -->
                    @if ($hrPlan->notes)
                        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                            <h4
                                class="font-extrabold text-xs uppercase text-slate-400 tracking-wider flex items-center gap-2 mb-3">
                                <i class="fas fa-sticky-note text-[#0B1329]"></i>
                                {{ __('Catatan Perencanaan SDM') }}
                            </h4>
                            <p
                                class="text-xs text-slate-500 leading-relaxed font-semibold bg-slate-50 p-4 rounded-xl border border-slate-100/60 whitespace-pre-line">
                                {{ $hrPlan->notes }}
                            </p>
                        </div>
                    @endif
                @endif
            @elseif ($tab === 'gantt')
                <!-- ================= GANTT ================= -->
                @include('project-planning.timeline.partials.gantt-chart', [
                    'wbsItems' => $wbsItems,
                    'projectDurationDays' => $projectDurationDays,
                    'minDate' => $minDate,
                ])

                @include('project-planning.timeline.partials.assign-modal',[
                    'project'=>$project,
                    'teamMembers'=>$teamMembers
                ])
            @elseif ($tab === 'budget')
                <!-- ================= BUDGET ================= -->
                <div class="p-10 text-center text-slate-400">
                    Budget page belum dibuat
                </div>

            @endif
        </div>
    </div>
</x-app-layout>