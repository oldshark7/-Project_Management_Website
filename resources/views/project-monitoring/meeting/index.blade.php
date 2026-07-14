<x-app-layout>
    <x-slot name="header">
        <x-header-component mode="costControl" />
    </x-slot>

    <div class="bg-white p-4 rounded-2xl border border-slate-100 min-h-full h-fit shadow-sm p-6 max-w-full mx-auto">
        <div class="mb-6 flex justify-between items-center border-b border-slate-100 pb-5">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    {{ __('MEETING SCHEDULE') }}
                </div>
                <h1 class="font-semibold text-3xl">{{ __('Meeting Schedule') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Create your meet here.') }}</p>
            </div>
        </div>
        <button type="button" onclick="openMeetingModal()"
            class="mb-4 inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition">
            <i class="fas fa-calendar-plus text-[11px]"></i>
            <span>Schedule Meeting</span>
        </button>

        @include('project-monitoring.meeting.partials.meeting-table')
        @include('project-monitoring.meeting.partials.add-meeting-modal')
        @include('project-monitoring.meeting.partials.detail-meeting-modal')
        @include('project-monitoring.meeting.partials.edit-meeting-modal')
        @include('project-monitoring.meeting.partials.delete-meeting-modal')
</x-app-layout>
