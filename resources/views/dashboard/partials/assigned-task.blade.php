<!-- project analystic card -->
<div class="lg:col-span-3 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
    
    <!-- upper section -->
    <div class="flex items-center justify-between mb-4">
        <!-- title section -->
        <h2 class="card-title text-black">
            {{ __('My Assigned Task') }}
        </h2>
    </div>

    <!-- asisigned task table -->
    <div>
        @include('dashboard.partials.task-table')
    </div>
</div>