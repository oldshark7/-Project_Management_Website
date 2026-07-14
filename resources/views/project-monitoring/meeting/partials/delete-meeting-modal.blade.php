<div id="delete-meeting-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDeleteMeetingModal()">
        </div>
        <!-- Modal -->
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100">
            <form id="deleteMeetingForm" method="POST">
                @csrf
                @method('DELETE')

                <div class="p-8 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center">
                        <i class="fas fa-trash text-red-500 text-2xl"></i>
                    </div>

                    <h2 class="mt-5 text-lg font-extrabold text-slate-800">
                        Delete Meeting Schedule?
                    </h2>

                    <p class="mt-3 text-sm text-slate-500">
                        Are you sure you want to delete
                        <span id="deleteMeetingTitle" class="font-bold text-slate-700"></span>?
                    </p>

                    <p class="text-xs text-red-500 mt-2">
                        This action cannot be undone.
                    </p>
                </div>

                <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex justify-end gap-3">
                    <button type="button" onclick="closeDeleteMeetingModal()"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-100">
                        Cancel
                    </button>

                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function openDeleteMeetingModal(id, title) {
        document.getElementById("deleteMeetingForm").action = "/meeting-schedules/" + id;
        document.getElementById("deleteMeetingTitle").innerText = '"' + title + '"';
        document.getElementById("delete-meeting-modal").classList.remove("hidden");
        document.body.classList.add("overflow-hidden");
    }

    function closeDeleteMeetingModal() {
        document.getElementById("delete-meeting-modal").classList.add("hidden");
        document.body.classList.remove("overflow-hidden");
    }
</script>
