<div class="border border-slate-200 bg-white rounded-2xl p-6 shadow-sm flex flex-col justify-between h-full">
    <!-- Title -->
    <h2 class="card-title text-black">
        {{ __('Reminders') }}
    </h2>

    @if ($nextMeeting)
        <div class="my-8">
            <h2 class="text-2xl font-bold text-zoomButton">
                {{ $nextMeeting->title }}
            </h2>

            <p class="mt-2 text-slate-500 text-sm">
                <i class="fas fa-folder-open mr-1"></i>
                {{ $nextMeeting->project->title ?? '-' }}
            </p>

            <p class="mt-3 text-slate-400">
                <i class="far fa-calendar mr-2"></i>
                {{ \Carbon\Carbon::parse($nextMeeting->meeting_date)->translatedFormat('d F Y') }}
            </p>

            <p class="text-slate-400">
                <i class="far fa-clock mr-2"></i>

                {{ \Carbon\Carbon::parse($nextMeeting->start_time)->format('H:i') }}
                -
                {{ \Carbon\Carbon::parse($nextMeeting->end_time)->format('H:i') }}
            </p>
        </div>

        @if ($nextMeeting->meeting_link)
            <a href="{{ $nextMeeting->meeting_link }}" target="_blank"
                class="bg-gradient-to-br from-gradientBlue to-blue-700 font-semibold text-white text-lg rounded-full py-4 text-center hover:opacity-90 transition">
                <i class="fas fa-video mr-3"></i>
                Join Meeting
            </a>
        @else
            <button class="bg-slate-200 text-slate-500 font-semibold text-lg rounded-full py-4 cursor-not-allowed">
                <i class="fas fa-video mr-3"></i>
                No Meeting Link
            </button>
        @endif
    @else
        <div class="flex-1 flex flex-col items-center justify-center py-12">
            <i class="far fa-calendar-check text-5xl text-slate-300 mb-4"></i>
            <h3 class="font-bold text-slate-600">
                No Scheduled Meeting
            </h3>

            <p class="text-sm text-slate-400 mt-1">
                You don't have any upcoming meetings.
            </p>
        </div>

        <button class="bg-slate-200 text-slate-500 font-semibold text-lg rounded-full py-4 cursor-not-allowed">
            <i class="fas fa-video mr-3"></i>
            Join Meeting
        </button>
    @endif
</div>
