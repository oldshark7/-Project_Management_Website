<div id="detail-meeting-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-8 text-center">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDetailMeetingModal()"></div>

        <!-- Modal -->
        <div class="relative inline-block w-full max-w-2xl overflow-hidden text-left align-middle transition-all transform bg-white border border-slate-100 shadow-xl rounded-2xl">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-calendar-day"></i>
                        Meeting Detail
                    </h2>

                    <p class="text-xs text-slate-400 mt-1">
                        Complete information about this meeting schedule.
                    </p>
                </div>

                <button onclick="closeDetailMeetingModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Project
                        </p>

                        <p id="detail-project" class="mt-1 text-sm font-semibold text-slate-700">
                            -
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Meeting Type
                        </p>

                        <p id="detail-type" class="mt-1 text-sm font-semibold text-slate-700">
                            -
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                        Meeting Title
                    </p>

                    <p id="detail-title" class="mt-1 text-sm font-semibold text-slate-700">
                        -
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Date
                        </p>

                        <p id="detail-date" class="mt-1 text-sm font-semibold text-slate-700">
                            -
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Start Time
                        </p>

                        <p id="detail-start-time" class="mt-1 text-sm font-semibold text-slate-700">
                            -
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            End Time
                        </p>

                        <p id="detail-end-time" class="mt-1 text-sm font-semibold text-slate-700">
                            -
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Location
                        </p>

                        <p id="detail-location" class="mt-1 text-sm font-semibold text-slate-700 break-all">
                            -
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Meeting Link
                        </p>

                        <a id="detail-link" href="#" target="_blank"
                            class="mt-1 block text-sm text-blue-600 hover:underline break-all">
                            -
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Reminder
                        </p>

                        <p id="detail-reminder" class="mt-1 text-sm font-semibold text-slate-700">
                            -
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Status
                        </p>

                        <span id="detail-status" class="inline-flex px-3 py-1 mt-1 text-xs font-bold rounded-full">
                            -
                        </span>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                        Description
                    </p>

                    <div id="detail-description"
                        class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 min-h-[80px]">
                        -
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5 pt-2 border-t border-slate-100">
                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Created By
                        </p>

                        <p id="detail-created-by" class="mt-1 text-sm font-semibold text-slate-700">
                            -
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Created At
                        </p>

                        <p id="detail-created-at" class="mt-1 text-sm font-semibold text-slate-700">
                            -
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end px-6 py-4 bg-slate-50 border-t border-slate-100">
                <button onclick="closeDetailMeetingModal()"
                    class="px-5 py-2 text-xs font-bold text-slate-700 transition border border-slate-200 rounded-xl hover:bg-slate-100">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openDetailMeetingModal(meeting) {

        document.getElementById('detail-project').innerText = meeting.project?.title ?? '-';
        document.getElementById('detail-title').innerText = meeting.title;
        document.getElementById('detail-type').innerText = meeting.meeting_type;
        document.getElementById('detail-date').innerText = meeting.meeting_date;
        document.getElementById('detail-start-time').innerText = meeting.start_time.substring(0, 5);
        document.getElementById('detail-end-time').innerText = meeting.end_time ? meeting.end_time.substring(0, 5) :
        '-';
        document.getElementById('detail-location').innerText = meeting.location ?? '-';

        const link = document.getElementById('detail-link');
        if (meeting.meeting_link) {
            link.href = meeting.meeting_link;
            link.innerText = meeting.meeting_link;
        } else {
            link.href = '#';
            link.innerText = '-';
        }

        document.getElementById('detail-reminder').innerText =
            meeting.reminder_before + " Minutes Before";

        const status = document.getElementById('detail-status');
        status.innerText = meeting.status;

        status.className = "inline-flex px-3 py-1 mt-1 text-xs font-bold rounded-full";

        if (meeting.status === "Scheduled") {
            status.classList.add("bg-blue-100", "text-blue-700");
        } else if (meeting.status === "Completed") {
            status.classList.add("bg-green-100", "text-green-700");
        } else {
            status.classList.add("bg-red-100", "text-red-700");
        }

        document.getElementById('detail-description').innerText = meeting.description ?? '-';
        document.getElementById('detail-created-by').innerText = meeting.creator?.name ?? '-';
        document.getElementById('detail-created-at').innerText = meeting.created_at ?? '-';
        document.getElementById('detail-meeting-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDetailMeetingModal() {
        document.getElementById('detail-meeting-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>
