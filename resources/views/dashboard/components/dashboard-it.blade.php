<!-- General status section for first row-->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
    @if ($showCards)
        @foreach ($cards as $card)
            <x-status-card 
            :label="$card['label']" 
            :titleColor="$card['titleColor']" 
            :infoColor="$card['infoColor']" 
            :valueColor="$card['valueColor']" 
            :value="$card['value']" 
            :route="$card['route']"
            :background="$card['background']" />
        @endforeach
    @endif
</div>

<!-- Workflow Progress & Next Actions for second row-->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
    @include('dashboard.partials.assigned-task')
    {{-- @include('dashboard.partials.reminders') --}}
    @include('dashboard.partials.workload')
</div>
