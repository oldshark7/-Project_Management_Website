<div id="meeting-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeMeetingModal()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100">

            <form action="{{ route('meeting-schedules.store') }}" method="POST">
                @csrf

                <div class="bg-white px-6 pt-6 pb-4">

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                        <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-calendar-plus"></i>
                            Schedule Meeting
                        </h3>

                        <button type="button" onclick="closeMeetingModal()"
                            class="text-slate-400 hover:text-slate-600 transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="space-y-4">

                        <!-- Project -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Project
                            </label>
                            
                            <select name="project_id" required
                                class="w-full rounded-xl border-slate-200 text-xs shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100">

                                <option value="">Select Project</option>

                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">
                                        {{ $project->title }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Meeting Title
                            </label>

                            <input type="text" name="title" required placeholder="Progress Meeting Week 1" class="w-full rounded-xl border-slate-200 text-xs shadow-sm font-semibold">
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Meeting Type
                            </label>

                            <select name="meeting_type" required class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
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
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Date
                                </label>

                                <input type="date" name="meeting_date" required min="{{ now()->toDateString() }}" class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Start Time
                                </label>

                                <input type="time" name="start_time" required class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    End Time
                                </label>

                                <input  type="time" name="end_time" required class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Location
                            </label>

                            <input type="text" name="location" placeholder="Meeting Room A" class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                        </div>

                        <!-- Meeting Link -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Meeting Link
                            </label>

                            <input type="url" name="meeting_link" required placeholder="https://meet.google.com/..."class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                        </div>

                        <!-- Reminder -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Reminder
                            </label>

                            <select name="reminder_before" class="w-full rounded-xl border-slate-200 text-xs shadow-sm">
                                <option value="5">5 Minutes Before</option>
                                <option value="10">10 Minutes Before</option>
                                <option value="15">15 Minutes Before</option>
                                <option value="30" selected>30 Minutes Before</option>
                                <option value="60">1 Hour Before</option>
                                <option value="1440">1 Day Before</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Description
                            </label>

                            <textarea rows="3" name="description" class="w-full rounded-xl border-slate-200 text-xs shadow-sm"
                                placeholder="Describe the meeting agenda..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex justify-end gap-2">

                    <button type="button" onclick="closeMeetingModal()"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-100">
                        Cancel
                    </button>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm">
                        Schedule Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openMeetingModal() {
        document.getElementById('meeting-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeMeetingModal() {
        document.getElementById('meeting-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    const meetingDate = document.querySelector('input[name="meeting_date"]');
    const startTime = document.querySelector('input[name="start_time"]');

    function updateMinimumStartTime() {
        if (!meetingDate.value) {
            startTime.removeAttribute('min');
            return;
        }

        const today = new Date().toISOString().split('T')[0];

        if (meetingDate.value === today) {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30);

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            startTime.min = `${hours}:${minutes}`;

            // kalau user sudah memilih jam yang lebih kecil, reset
            if (startTime.value && startTime.value < startTime.min) {
                startTime.value = '';
            }
        } else {
            startTime.removeAttribute('min');
        }
    }

    meetingDate.addEventListener('change', updateMinimumStartTime);
    window.addEventListener('load', updateMinimumStartTime);
</script>
