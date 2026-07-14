<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">
                <i class="fas fa-robot text-violet-600"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">
                    AI Cost Insight
                </h3>
                <p class="text-xs text-slate-500">
                    AI analysis for project cost monitoring
                </p>
            </div>
        </div>

        <button
            id="generateInsightBtn"
            data-url="{{ route('cost-control.generate-insight', $project) }}"
            class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold">
            Generate Insight
        </button>
    </div>

    <div
        id="emptyInsight"
        class="py-16 px-8 text-center">

        <div class="mx-auto w-20 h-20 rounded-full bg-violet-100 flex items-center justify-center">
            <i class="fas fa-robot text-3xl text-violet-600"></i>
        </div>

        <h3 class="mt-6 text-lg font-bold text-slate-700">
            AI Insight Belum Dibuat
        </h3>

        <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto leading-6">
            Klik tombol <strong>Generate Insight</strong> untuk menganalisis
            kondisi budget proyek, menemukan potensi masalah,
            serta mendapatkan rekomendasi dari AI.
        </p>
    </div>

    <div id="insightContent" class="hidden p-6 space-y-6">
        <!-- Top -->
        <div class="grid grid-cols-3 gap-5">
            <div id="budgetHealthCard" class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-xs uppercase text-slate-500 font-bold">
                    Budget Health
                </p>
                <h2 id="budgetHealthTitle"
                    class="text-3xl font-black text-slate-700 mt-2">
                    -
                </h2>
                <p id="budgetHealthSummary"
                    class="text-sm text-slate-500 mt-2">
                    Click "Generate Insight" to analyze project cost.
                </p>
            </div>
            <div class="col-span-2 rounded-xl border border-slate-100 bg-slate-50 p-5">
                <p class="text-xs uppercase text-slate-500 font-bold mb-3">
                    Executive Summary
                </p>
                <p id="executiveSummary" class="text-sm text-slate-600 leading-7">
                    AI analysis has not been generated yet.
                </p>
            </div>
        </div>

        <!-- Findings -->
        <div>
            <h4 class="font-bold text-slate-700 mb-3">
                Key Findings
            </h4>
            <div id="keyFindings" class="space-y-3">
                <div class="rounded-xl border border-dashed border-slate-300 p-5 text-center">
                    <p class="text-sm text-slate-400">
                        No findings yet.
                    </p>
                </div>
            </div>
        </div>
        <!-- Recommendation -->
        <div>
            <h4 class="font-bold text-slate-700 mb-3">
                AI Recommendation
            </h4>

            <div id="recommendations" class="rounded-xl bg-slate-50 border border-slate-200 p-5">
                <p class="text-sm text-slate-400">
                    No recommendations available.
                </p>
            </div>
        </div>
    </div>
</div>
