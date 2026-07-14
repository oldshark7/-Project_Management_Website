<div id="edit-meeting-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity" onclick="closeEditMeetingModal()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100">

            <form id="editMeetingForm" method="POST">
                @csrf
                @method('PUT')

                <div class="bg-white px-6 pt-6 pb-4">

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                        <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-pen"></i>
                            Edit Meeting Schedule
                        </h3>

                        <button type="button" onclick="closeEditMeetingModal()"
                            class="text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="space-y-4">

                        <!-- Project -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                Project
                            </label>

                            <select id="edit_project_id" name="project_id" required
                                class="w-full rounded-xl border-slate-200 text-xs shadow-sm">

                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">
                                        {{ $project->title }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                Meeting Title
                            </label>

                            <input id="edit_title" type="text" name="title" required
                                class="w-full rounded-xl border-slate-200 text-xs shadow-sm font-semibold">
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                Meeting Type
                            </label>

                            <select id="edit_meeting_type" name="meeting_type"
                                class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                                <option>Project Kickoff</option>
                                <option>Progress Meeting</option>
                                <option>Coordination Meeting</option>
                                <option>Client Meeting</option>
                                <option>Issue Discussion</option>
                                <option>Risk Review</option>
                                <option>Change Request Review</option>
                                <option>Project Closing</option>
                                <option>Other</option>
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                    Date
                                </label>

                                <input id="edit_meeting_date" type="date" name="meeting_date"
                                    min="{{ now()->toDateString() }}" required
                                    class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                    Start Time
                                </label>

                                <input id="edit_start_time" type="time" name="start_time" required
                                    class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                    End Time
                                </label>

                                <input id="edit_end_time" type="time" name="end_time" required
                                    class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                Location
                            </label>

                            <input id="edit_location" type="text" name="location"
                                class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                        </div>

                        <!-- Meeting Link -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                Meeting Link
                            </label>

                            <input id="edit_meeting_link" type="url" name="meeting_link"
                                class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                        </div>

                        <!-- Reminder -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                Reminder
                            </label>

                            <select id="edit_reminder_before" name="reminder_before"
                                class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                                <option value="5">5 Minutes Before</option>
                                <option value="10">10 Minutes Before</option>
                                <option value="15">15 Minutes Before</option>
                                <option value="30">30 Minutes Before</option>
                                <option value="60">1 Hour Before</option>
                                <option value="1440">1 Day Before</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                Status
                            </label>

                            <select id="edit_status" name="status" class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                                <option value="Scheduled">Scheduled</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">
                                Description
                            </label>

                            <textarea id="edit_description" rows="3" name="description"
                                class="w-full rounded-xl border-slate-200 text-xs shadow-sm"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeEditMeetingModal()"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-100">
                        Cancel
                    </button>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm">
                        Update Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditMeetingModal(meeting) {

        document.getElementById('editMeetingForm').action = "/meeting-schedules/" + meeting.id;

        document.getElementById('edit_project_id').value = meeting.project_id;
        document.getElementById('edit_title').value = meeting.title;
        document.getElementById('edit_meeting_type').value = meeting.meeting_type;
        document.getElementById('edit_meeting_date').value = meeting.meeting_date;
        document.getElementById('edit_start_time').value = meeting.start_time.substring(0, 5);
        document.getElementById('edit_end_time').value = meeting.end_time ? meeting.end_time.substring(0, 5) : "";
        document.getElementById('edit_location').value = meeting.location ?? "";
        document.getElementById('edit_meeting_link').value = meeting.meeting_link ?? "";
        document.getElementById('edit_reminder_before').value = meeting.reminder_before;
        document.getElementById('edit_status').value = meeting.status;
        document.getElementById('edit_description').value = meeting.description ?? "";

        document.getElementById('edit-meeting-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeEditMeetingModal() {
        document.getElementById('edit-meeting-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>
