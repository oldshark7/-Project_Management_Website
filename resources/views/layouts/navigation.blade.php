<nav class="rounded-2xl bg-[#0B1329] border border-slate-900 shadow-xl h-full flex flex-col justify-between p-5">
    <div>
        <!-- Logo -->
        <div class="flex items-center px-2 py-4 mb-4">
            <x-application-logo/>
        </div>

        <!-- Menu Links -->
        <div class="flex flex-col gap-1.5 px-2">
            <x-nav-link :active="request()->routeIs('dashboard')" href="{{ route('dashboard') }}">
                <i class="fa-solid fa-table-columns text-base"></i>
                <span>{{ __('Dashboard') }}</span>
            </x-nav-link>

            @if (Auth::check() && in_array(strtolower(Auth::user()->role), ['project manager', 'manager']))
                <x-nav-link :active="request()->routeIs('projects.index')" href="{{ route('projects.index') }}">
                    <i class="fas fa-rocket text-base"></i>
                    <span>{{ __('Inisiasi Proyek') }}</span>
                </x-nav-link>
            @endif

            @if (Auth::check() && in_array(strtolower(Auth::user()->role), ['manager', 'project management officer']))
                <x-nav-link :active="request()->routeIs('project-planning')" href="{{ route('project-planning') }}">
                    <i class="fa-regular fa-calendar text-base"></i>
                    <span>{{ __('Perencanaan Proyek') }}</span>
                </x-nav-link>
            @endif

            @if (Auth::check() && in_array(strtolower(Auth::user()->role), ['project management officer','project manager','manager','it']))
                <x-nav-link :active="request()->routeIs('meeting-schedules.*')"
                    href="{{ route('meeting-schedules.index') }}">
                    <i class="fas fa-calendar-check text-base"></i>
                    <span>{{ __('Meeting Schedule') }}</span>
                </x-nav-link>
            @endif

            <x-nav-link :active="request()->routeIs('teamManagement')" href="{{ route('teamManagement') }}">
                <i class="fas fa-users text-base"></i>
                <span>{{ __('Manajemen Tim') }}</span>
            </x-nav-link>

            @if (Auth::check() && in_array(strtolower(Auth::user()->role), ['it','project management officer']))
                <x-nav-link :active="request()->routeIs('task-management.index')" href="{{ route('task-management.index') }}">
                    <i class="fas fa-tasks text-base"></i>
                    <span>{{ __('Manajemen Task') }}</span>
                </x-nav-link>
            @endif

            @if (Auth::check() && in_array(strtolower(Auth::user()->role), ['project management officer', 'manager']))
                <x-nav-link :active="request()->routeIs('cost-control.index')" href="{{ route('cost-control.index') }}">
                    <i class="fas fa-money-bill-wave text-base"></i>
                    <span>{{ __('Control Cost') }}</span>
                </x-nav-link>
            @endif

            @if (Auth::check() && in_array(strtolower(Auth::user()->role), ['project management officer', 'it']))
                <x-nav-link :active="request()->routeIs('issue-risk.index')" href="{{ route('issue-risk.index') }}">
                    <i class="fas fa-bug text-base"></i>
                    <span>{{ __('Issue and Risk') }}</span>
                </x-nav-link>
            @endif

            @if (Auth::check() && in_array(strtolower(Auth::user()->role), ['project management officer']))
                <x-nav-link :active="request()->routeIs('change-requests.index')" href="{{ route('change-requests.index') }}">
                    <i class="fas fa-exchange-alt text-base"></i>
                    <span>{{ __('Change Request') }}</span>
                </x-nav-link>
            @endif
        </div>
    </div>

    <!-- Bottom Menu -->
    <div class="px-2 pt-4 border-t border-[#1E293B] flex flex-col gap-1.5">
        <!-- Settings Link -->
        <x-nav-link :active="request()->routeIs('profile.edit')" href="{{ route('profile.edit') }}">
            <i class="fa-solid fa-gear text-base"></i>
            <span>{{ __('Pengaturan') }}</span>
        </x-nav-link>

        <!-- Logout Link -->
        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all text-slate-400 hover:bg-rose-950/30 hover:text-rose-400">
            <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
            <span>{{ __('Keluar') }}</span>
        </a>
    </div>
</nav>
