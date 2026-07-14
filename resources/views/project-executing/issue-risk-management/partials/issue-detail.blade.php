<div id="issue-detail-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">

    <div class="flex items-center justify-center min-h-screen px-4 py-8">

        <!-- Overlay -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDetailModal()">
        </div>

        <!-- Modal -->
        <div
            class="relative z-10 w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
            <!-- Header -->
            <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between">

                <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-bug"></i>
                    Issue Detail
                </h3>

                <button onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <!-- Body -->
            <div class="px-6 py-5 space-y-5">

                <!-- Title -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Title
                    </label>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p id="detail-title" class="text-sm font-semibold"></p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Description
                    </label>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 min-h-[100px]">
                        <p id="detail-description" class="text-sm whitespace-pre-line"></p>
                    </div>
                </div>

                <!-- Status & Priority -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            Status
                        </label>

                        <select id="detail-status"
                            class="w-full rounded-xl border-slate-200 text-xs shadow-sm focus:border-slate-800 focus:ring-slate-100">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="done">Done</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            Priority
                        </label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p id="detail-priority"></p>
                        </div>
                    </div>
                </div>

                <!-- Assignee & Reporter -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            Assignee
                        </label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p id="detail-assignee"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            Reporter
                        </label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p id="detail-reporter"></p>
                        </div>
                    </div>
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Due Date
                    </label>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p id="detail-due"></p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
                <button onclick="closeDetailModal()"
                    class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-100">
                    Close
                </button>

                <button onclick="updateStatus(currentIssueId)"
                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">
                    Update Status
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    function updateStatus(issueId) {
        let status = document.getElementById('detail-status').value;

        fetch(`/issues/${issueId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    status: status
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
            });
    }
</script>
