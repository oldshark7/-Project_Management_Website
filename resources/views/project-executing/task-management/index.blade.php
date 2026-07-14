<x-app-layout>
    <x-slot name="header">
        <x-header-component mode="task" />
    </x-slot>

    <div class="bg-white p-4 rounded-2xl border border-slate-100 h-full shadow-sm p-6 max-w-full mx-auto">
        <div class="mb-6 flex justify-between items-center border-b border-slate-100 pb-5">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    {{ __('Task Management') }}
                </div>
                <h1 class="font-semibold text-3xl">{{ __('Task Management') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Manage your tasks efficiently.') }}</p>
            </div>
        </div>
        <x-project-list-table :projects="$projects" route="task-management.show" />
    </div>
</x-app-layout>
