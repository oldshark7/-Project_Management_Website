<!-- project analystic card -->
<div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">

    <!-- upper section -->
    <div class="flex items-center justify-between mb-8">
        <!-- title section -->
        <h2 class="card-title text-black">
            {{ __('Projects Analytic') }}
        </h2>

        <!-- symbol description -->
        <div class="flex items-center gap-4 text-[10px] font-bold text-slate-500">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 bg-blue-600 rounded-full"></span>
                <span>{{ __('Planned') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 bg-emerald-600 rounded-full"></span>
                <span>{{ __('Done') }}</span>
            </div>
        </div>
    </div>

    <!-- bar chart section -->
    <div class="flex justify-around items-end h-52 px-4 pb-2 border-b border-slate-100">

        @php
            $max = max($projectAnalytics->max('planned'), $projectAnalytics->max('done'), 1);
        @endphp

        @foreach ($projectAnalytics as $item)
            @php
                $plannedHeight = ($item['planned'] / $max) * 100;
                $doneHeight = ($item['done'] / $max) * 100;
            @endphp

            <div class="flex flex-col items-center gap-2">
                <div class="h-40 w-14 bg-slate-200 rounded-full overflow-hidden flex flex-col justify-end">

                    {{-- Done --}}
                    <div class="bg-emerald-600 w-full" style="height: {{ $doneHeight }}%">
                    </div>

                    {{-- Planned --}}
                    <div class="bg-blue-600 w-full" style="height: {{ $plannedHeight }}%">
                    </div>

                </div>

                <span class="text-[10px] text-slate-400 font-bold">
                    {{ $item['month'] }}
                </span>
            </div>
        @endforeach
    </div>
</div>
