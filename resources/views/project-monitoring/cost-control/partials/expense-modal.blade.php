<form action="{{ route('cost-control.expense.store', $project) }}" method="POST">
    @csrf
    <div id="expense-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeExpenseModal()"></div>

            <!-- Modal -->
            <div
                class="relative z-10 w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <!-- Header -->
                <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-receipt"></i>
                        Record Expense
                    </h3>

                    <button
                        type="button"
                        onclick="closeExpenseModal()"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-100">
                        Cancel
                    </button>
                </div>

                <!-- Body -->
                <div class="px-6 py-5 space-y-5">
                    <!-- Budget Item Information -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                            Budget Item
                        </label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 space-y-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p id="expense-category" class="text-sm font-bold text-slate-800">
                                        Human Resource
                                    </p>

                                    <p id="expense-description" class="text-xs text-slate-500 mt-1">
                                        Team salary and operational costs
                                    </p>
                                </div>

                                <div
                                    class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-[10px] font-bold uppercase">
                                    Budget Item
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                        Planned
                                    </p>

                                    <p id="expense-planned-cost" class="mt-1 text-sm font-bold text-slate-800">
                                        Rp 4.000.000
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                        Current Actual
                                    </p>

                                    <p id="expense-current-actual" class="mt-1 text-sm font-bold text-orange-500">
                                        Rp 1.500.000
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                        Remaining
                                    </p>

                                    <p id="expense-current-remaining" class="mt-1 text-sm font-bold text-green-600">
                                        Rp 2.500.000
                                    </p>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                        After This Expense
                                    </p>

                                    <span id="expense-status" class="text-[10px] font-bold text-green-600">
                                        Within Budget
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                            Actual Cost
                                        </p>

                                        <p id="expense-preview-actual" class="mt-1 text-sm font-bold">
                                            Rp 1.500.000
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                            Remaining Budget
                                        </p>

                                        <p id="expense-preview-remaining" class="mt-1 text-sm font-bold text-green-600">
                                            Rp 2.500.000
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="expense-budget-item-id" name="budget_item_id">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Amount -->
                        <div>
                            <label
                                class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Amount
                            </label>
                            <input required name="amount" type="number" id="expense-amount"
                                class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-slate-800 focus:ring-slate-100"
                                placeholder="0">
                        </div>

                        <!-- Expense Date -->
                        <div>
                            <label
                                class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Expense
                                Date</label>

                            <input name="expense_date" type="date"
                                class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-slate-800 focus:ring-slate-100">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label
                            class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Description
                        </label>
                        <textarea rows="4" name="description"
                            class="w-full rounded-xl border-slate-200 text-sm shadow-sm resize-none focus:border-slate-800 focus:ring-slate-100"
                            placeholder="Additional notes..."></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
                    <button onclick="closeExpenseModal()"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold hover:bg-slate-100">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">
                        Save Expense
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
