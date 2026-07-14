<div id="changeRequestDetailModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true"
            onclick="closeChangeRequestDetailModal()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal -->
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-100">

            <!-- Header -->
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                    <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2"><i class="fas fa-code-branch text-slate-900"></i>Detail Change Request</h3>

                    <button type="button" onclick="closeChangeRequestDetailModal()" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="space-y-5">
                    <!-- Task -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Task</label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p id="crTaskTitle" class="text-sm font-semibold text-slate-800"></p>
                        </div>
                    </div>

                    <!-- Before & After -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Before</label>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 min-h-[140px]">
                                <p id="crOldValue" class="text-sm text-slate-700 whitespace-pre-line"></p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">After</label>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 min-h-[140px]">
                                <p id="crNewValue" class="text-sm text-slate-700 whitespace-pre-line"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Reason</label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 min-h-[100px]">
                            <p id="crReason" class="text-sm text-slate-700 whitespace-pre-line"></p>
                        </div>
                    </div>

                    <!-- Status & Date -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p id="crStatus"class="text-sm font-semibold capitalize"></p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Request Date</label>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p id="crDate" class="text-sm font-semibold text-slate-700"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Requested By -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Requested By</label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p id="crRequestedBy" class="text-sm font-semibold text-slate-700"></p>
                        </div>
                    </div>

                    <!-- Requested Deadline -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Requested Deadline
                        </label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p id="crRequestedDeadline" class="text-sm font-semibold text-slate-700"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                <button
                    type="button"
                    onclick="closeChangeRequestDetailModal()"
                    class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-100 rounded-xl text-xs font-bold transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>