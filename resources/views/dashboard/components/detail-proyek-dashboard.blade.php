<x-app-layout>
    <x-slot name="header">
        <x-header-component :showSearch="true" mode="dashboard" />
    </x-slot>

    <div class="flex flex-col">
        <div class="bg-white rounded-2xl border-slate-100 border shadow-sm p-6 w-full h-full ">

            <!-- section menu title -->
            <div class="flex justify-between items-center pb-5">
                <div class="flex flex-col">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <a href="{{ route('dashboard') }}"
                            class="text-[10px] hover:text-slate-500 font-bold text-slate-400 uppercase tracking-widest">
                            <i class="fas fa-arrow-left text-[8px]"></i>
                            {{ __('Kembali | ') }}
                        </a>
                        {{ __('DASHBOARD' . ' / ' . 'PROJECT DETAIL' . ' - ' . $title) }}
                    </div>
                    <h1 class="font-semibold text-3xl">{{ $title ?? '-' }}</h1>
                    <p class="text-sm text-slate-500 mt-2">{{ $project->proposal->background ?? '-' }}</p>
                </div>
            </div>

            <div class="flex justify-between items-center">
                @include('dashboard.partials.project-date')
                <a href="{{ route('report.weekly', $project->id) }}"
                    class="px-4 py-2 bg-gradient-to-br from-blue-600 to-gradientBlue text-white rounded-full text-lg">
                    <i class="fas fa-file-pdf mr-2"></i>Download Report
                </a>
            </div>

            <!-- General status section for first row-->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4 mt-6">
                @if ($showCards)
                    @foreach ($cards as $card)
                        <x-status-card :label="$card['label']" :titleColor="$card['titleColor']" :infoColor="$card['infoColor']" :valueColor="$card['valueColor']"
                            :value="$card['value']" :background="$card['background']" />
                    @endforeach
                @endif
            </div>

            <div class="grid grid-cols-4 gap-4">
                <div class="col-span-2 rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
                    <h1 class="card-title text-black">
                        Performa PIC / Team
                    </h1>
                    @include('dashboard.partials.pic-table')
                    <x-button-link :url="url('/projects/' . $project->id . '/human-resource')" buttonTitle="See Detail Team" />
                </div>

                <div class="col-span-2 rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
                    <h1 class="card-title text-black mb-4">
                        Task Breakdown
                    </h1>

                    <div class="w-full h-60 mb-4">
                        <canvas id="taskBreakdownChart"></canvas>
                    </div>

                    <div>
                        <x-button-link :url="url('/task-management/' . $project->id)" buttonTitle="See Detail Task" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@php
    $chartData = [$todoTasks, $inProgressTasks, $doneTasks, $overdueTasks];
@endphp
<script>
    const taskData = @json($chartData);
    const ctx = document.getElementById('taskBreakdownChart');

    const total = taskData.reduce((a, b) => a + b, 0);

    const labelsWithValue = [
        'To Do',
        'In Progress',
        'Done',
        'Overdue'
    ].map((label, index) => {
        const value = taskData[index];
        const percent = total > 0 ? Math.round((value / total) * 100) : 0;
        return `${label} (${value} - ${percent}%)`;
    });

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labelsWithValue,
            datasets: [{
                data: taskData,
                backgroundColor: [
                    '#bfbfbf',
                    '#2556A1',
                    '#22C55E',
                    '#fb923c'
                ],
                borderWidth: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '50%',
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
</script>
