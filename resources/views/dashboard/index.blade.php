<x-app-layout>
    <x-slot name="header">
        <x-header-component :projects="$projects" :showSearch="true" mode="dashboard" />
    </x-slot>

    <div class="flex flex-col">
        <div class="bg-white rounded-2xl border-slate-100 border shadow-sm p-6 max-w-full h-full">

            <!-- section menu title -->
            <div class="flex justify-between items-center pb-5 border-b border-slate-100 mb-6">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        {{ __('DASHBOARD') }}
                    </div>
                    <h1 class="font-semibold text-3xl">{{ __('Project Dashboard') }}</h1>
                    <p class="text-sm text-slate-500">{{ __('Plan, Prioritize, and accomplish your tasks with ease.') }}
                    </p>
                </div>

                <!-- Create Project Button (PM Only) -->
                @if (Auth::check() && strtolower(Auth::user()->role) === 'project manager')
                    <div>
                        <a href="{{ route('projects.create') }}"
                            class="w-full flex items-center justify-center gap-2 px-8 py-2.5 bg-gradient-to-br from-blue-600 to-gradientBlue hover:bg-blue-700 text-white rounded-full text-lg transition shadow-blue-500/10 hover:shadow-lg">
                            <i class="fas fa-plus text-[10px]"></i>
                            <span>{{ __('Add Project') }}</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- layout -->
            @if (Auth::check() && strtolower(Auth::user()->role) === 'project management officer')
                @include('dashboard.components.dashboard-pmo')
            @elseif (Auth::check() && strtolower(Auth::user()->role) === 'it')
                @include('dashboard.components.dashboard-it')
            @elseif (Auth::check() && strtolower(Auth::user()->role) === 'project manager')
                @include('dashboard.components.dashboard-pmo')
            @elseif (Auth::check() && strtolower(Auth::user()->role) === 'manager')
                @include('dashboard.components.dashboard-pmo')
            @else
                <h1> Not Allowed
            @endif
        </div>
    </div>
</x-app-layout>
