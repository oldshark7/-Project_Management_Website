<div id="taskDetailModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeTaskModal()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal -->
        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100">
            <!-- Header -->
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                    <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2"><i
                            class="fas fa-tasks text-slate-900"></i>Task Detail</h3>

                    <button type="button" onclick="closeTaskModal()"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="space-y-5">
                    <div>
                        <label
                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Task</label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p id="modalTaskTitle" class="text-sm font-semibold text-slate-800"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            Description
                        </label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 min-h-[90px]">
                            <p id="modalTaskDesc" class="text-sm text-slate-700 whitespace-pre-line"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Priority
                            </label>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p id="modalTaskPriority" class="text-sm font-semibold"></p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Due
                                Date</label>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p id="modalTaskDue" class="text-sm font-semibold text-slate-700"></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p id="modalTaskStatus" class="text-sm font-semibold"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeTaskModal()"
                    class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-100 rounded-xl text-xs font-bold transition">
                    Close
                </button>

                <button type="button" onclick="openChangeRequest()"
                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-xs font-bold transition shadow-sm">
                    Request Change
                </button>
            </div>
        </div>
    </div>
</div>
