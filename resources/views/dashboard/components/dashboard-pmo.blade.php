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
<div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
    <div class="grid grid-cols-3 col-span-3 gap-4">
        @include('dashboard.partials.project-analytic')
        @include('dashboard.partials.reminders')
        @include('dashboard.partials.recent-activity')
    </div>
    @include('dashboard.partials.next-action')
</div>
