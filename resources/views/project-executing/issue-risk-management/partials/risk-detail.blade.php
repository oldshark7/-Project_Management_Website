<div id="risk-detail-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between border-b px-6 py-4 flex-shrink-0">
            <h2 class="text-xl font-bold text-slate-700">
                Risk Detail
            </h2>

            <button onclick="closeRiskDetail()">
                <i class="fas fa-times text-slate-500 hover:text-red-500 transition"></i>
            </button>
        </div>

        <!-- Summary -->
        <div class="border-b bg-white px-6 py-5 flex-shrink-0">

            <div class="space-y-5">

                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Risk Title
                    </label>
                    <p id="risk-title" class="mt-1 text-xl font-semibold text-slate-700">
                        -
                    </p>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Risk Owner
                    </label>
                    <p id="risk-owner" class="mt-1 text-slate-700">
                        -
                    </p>
                </div>

                <div class="flex items-center gap-10">

                    <div>
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Probability
                        </label>

                        <div class="mt-2">
                            <span id="risk-probability"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                -
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Severity
                        </label>

                        <div class="mt-2">
                            <span id="risk-severity"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                -
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Status
                        </label>

                        <div class="mt-2">
                            <span id="risk-status"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                                -
                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <div>
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Description
                </label>
                <p id="risk-description" class="mt-2 whitespace-pre-line text-slate-700 leading-relaxed">
                    -
                </p>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Risk Cause
                </label>
                <p id="risk-cause" class="mt-2 whitespace-pre-line text-slate-700 leading-relaxed">
                    -
                </p>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Impact
                </label>
                <p id="risk-impact" class="mt-2 whitespace-pre-line text-slate-700 leading-relaxed">
                    -
                </p>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Mitigation Plan
                </label>
                <p id="risk-mitigation" class="mt-2 whitespace-pre-line text-slate-700 leading-relaxed">
                    -
                </p>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Contingency Plan
                </label>
                <p id="risk-contingency" class="mt-2 whitespace-pre-line text-slate-700 leading-relaxed">
                    -
                </p>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Notes
                </label>
                <p id="risk-notes" class="mt-2 whitespace-pre-line text-slate-700 leading-relaxed">
                    -
                </p>
            </div>

        </div>

    </div>

</div>
