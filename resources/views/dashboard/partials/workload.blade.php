<div class="border border-slate-200 bg-white col-span-2 rounded-2xl p-6 shadow-sm flex flex-col">
    <!-- title section -->
    <h2 class="card-title text-black mb-4">
        {{ __('Workload') }}
    </h2>

    <!-- list -->
    <div class="flex flex-col gap-4">
        @foreach ($members as $member)
            @php
                $barColor = 'bg-emerald-500';
                $wPercent = $member->current_workload_percentage;

                if ($wPercent >= 100) {
                    $barColor = 'bg-rose-500';
                } elseif ($wPercent > 80) {
                    $barColor = 'bg-amber-500';
                } elseif ($wPercent > 50) {
                    $barColor = 'bg-blue-500';
                }
            @endphp

            <div>
                <!-- nama -->
                <h3 class="text-sm font-semibold text-slate-700 mb-1">
                    {{ $member->name }}
                </h3>

                <!-- progress -->
                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full {{ $barColor }}" style="width: {{ min(100, $wPercent) }}%">
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
