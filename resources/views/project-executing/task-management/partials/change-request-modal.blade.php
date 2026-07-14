<div id="changeRequestModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity" onclick="closeChangeRequestModal()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-100">

            <!-- FORM -->
            <form>
                <!-- HEADER -->
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                        <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-code-branch"></i>
                            Change Request
                        </h3>

                        <button type="button" onclick="closeChangeRequestModal()" class="text-slate-400 hover:text-slate-600 transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="space-y-5">
                        <!-- Task -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                Task
                            </label>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                <span id="cr_task_title"></span>
                            </div>
                        </div>

                        <!-- Before After -->
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                    Current State
                                </label>
                                <textarea id="cr_current_state" rows="6" readonly class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 shadow-sm"></textarea>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                    Proposed State
                                    <span class="text-red-500">*</span>
                                </label>
                                <textarea required id="cr_proposed_state" rows="6" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100" placeholder="Jelaskan perubahan yang diinginkan..."></textarea>

                                <p id="error_proposed" class="hidden text-xs text-red-500 mt-1"></p>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                Reason
                                <span class="text-red-500">*</span>
                            </label>
                            <textarea required id="cr_reason" rows="3" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100" placeholder="Kenapa perubahan ini diperlukan?"></textarea>

                            <p id="error_reason" class="hidden text-xs text-red-500 mt-1"></p>
                        </div>

                        <!-- Impact -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                Impact
                            </label>

                            <select required  id="cr_impact" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100">
                                <option value="low">Low Impact</option>
                                <option value="medium">Medium Impact</option>
                                <option value="high">High Impact</option>
                            </select>
                        </div>

                        <!-- Deadline -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                Requested Deadline
                                <span class="text-red-500">*</span>
                            </label>

                            <input required  id="cr_deadline" type="date" min="{{ now()->toDateString() }}" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100">
                            <p id="error_deadline" class="hidden text-xs text-red-500 mt-1"></p>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeChangeRequestModal()" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-100 transition">
                        Batal
                    </button>

                    <button type="button" onclick="submitChangeRequest()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>