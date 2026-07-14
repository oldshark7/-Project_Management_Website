<div class="flex gap-2 mb-4">
    <!-- Assigned -->
    <x-dropdown>
        <x-slot name="trigger">
            <button class="px-3 py-2 border rounded-md text-sm">
                Assigned:
                {{ request('assigned') === 'me' ? 'Me' : 'All' }}
            </button>
        </x-slot>
        <x-slot name="content">
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['assigned' => null]) }}">
                All
            </x-dropdown-link>
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['assigned' => 'me']) }}">
                My Tasks
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>

    <!-- Priority -->
    <x-dropdown>
        <x-slot name="trigger">
            <button class="px-3 py-2 border rounded-md text-sm">
                Priority:
                {{ request('priority') ? ucfirst(request('priority')) : 'All' }}
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['priority' => null]) }}">
                All
            </x-dropdown-link>
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['priority' => 'low']) }}">
                Low
            </x-dropdown-link>
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['priority' => 'medium']) }}">
                Medium
            </x-dropdown-link>
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['priority' => 'high']) }}">
                High
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>

    <!-- Due -->
    <x-dropdown>
        <x-slot name="trigger">
            <button class="px-3 py-2 border rounded-md text-sm">
                Due:
                {{ request('due') ? ucfirst(request('due')) : 'All' }}
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['due' => null]) }}">
                All
            </x-dropdown-link>
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['due' => 'today']) }}">
                Today
            </x-dropdown-link>
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['due' => 'overdue']) }}">
                Overdue
            </x-dropdown-link>
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['due' => 'done']) }}">
                Done
            </x-dropdown-link>
            <x-dropdown-link href="{{ request()->fullUrlWithQuery(['due' => 'approved']) }}">
                Approved
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
</div>
