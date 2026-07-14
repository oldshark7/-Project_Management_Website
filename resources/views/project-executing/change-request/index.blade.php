<x-app-layout>
    <x-slot name="header">
        <x-header-component mode="changeRequest" />
    </x-slot>

    <div class="bg-white p-4 rounded-2xl border border-slate-100 h-full shadow-sm p-6 max-w-full mx-auto">
        <div class="mb-6 flex justify-between items-center border-b border-slate-100 pb-5">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    {{ __('CHANGE REQUEST') }}
                </div>
                <h1 class="font-semibold text-3xl">{{ __('Change Request') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Need to evaluate about the task? Let us help!') }}</p>
            </div>
        </div>
        <x-project-list-table :projects="$projects" route="change-requests.show" />
    </div>
</x-app-layout>
