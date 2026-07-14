<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="h-full overflow-y-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <th class="px-6 py-4 w-16 text-center">No</th>
                    <th class="px-6 py-4 w-64">Title</th>
                    <th class="px-6 py-4">Description</th>
                    <th class="px-6 py-4 w-32 text-center">Probability</th>
                    <th class="px-6 py-4 w-32 text-center">Severity</th>
                    <th class="px-6 py-4 w-32 text-center">Status</th>
                    <th class="px-6 py-4 w-56">Risk Owner</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                @forelse($risks as $risk)
                    <tr class="hover:bg-slate-50 transition duration-150 cursor-pointer"
                        onclick='openRiskDetail(@json($risk))'>

                        <td class="px-6 py-4 text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">
                                {{ $risk->risk_title }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">
                                Risk ID : RSK-{{ str_pad($risk->id, 3, '0', STR_PAD_LEFT) }}
                            </div>
                        </td>

                        <td class="px-6 py-4 max-w-md truncate"
                            title="{{ $risk->risk_description }}">
                            {{ $risk->risk_description }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            @php
                                $probabilityClass = match(strtolower($risk->probability)){
                                    'high' => 'bg-red-100 text-red-700',
                                    'medium' => 'bg-amber-100 text-amber-700',
                                    'low' => 'bg-emerald-100 text-emerald-700',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $probabilityClass }}">
                                {{ ucfirst($risk->probability) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @php
                                $severityClass = match(strtolower($risk->severity)){
                                    'high' => 'bg-red-100 text-red-700',
                                    'medium' => 'bg-amber-100 text-amber-700',
                                    'low' => 'bg-emerald-100 text-emerald-700',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $severityClass }}">
                                {{ ucfirst($risk->severity) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @php
                                $statusClass = match(strtolower($risk->status)){
                                    'open' => 'bg-blue-100 text-blue-700',
                                    'closed' => 'bg-emerald-100 text-emerald-700',
                                    'monitoring' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($risk->status) }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-full bg-[#0B1329] text-white flex items-center justify-center font-black text-[9px] uppercase tracking-wider">
                                    {{ strtoupper(substr($risk->risk_owner ?? 'NA',0,2)) }}
                                </div>
                                {{ $risk->risk_owner }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="fas fa-shield-halved text-5xl mb-4"></i>
                                <p class="text-lg font-semibold text-slate-500">Belum ada Risk</p>
                                <p class="text-sm">Silakan membuat risk terlebih dahulu.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
    let currentRiskId = null;

    function openRiskDetail(risk) {

        currentRiskId = risk.id;

        document.getElementById('risk-title').innerText = risk.risk_title ?? '-';
        document.getElementById('risk-description').innerText = risk.risk_description ?? '-';
        document.getElementById('risk-cause').innerText = risk.risk_cause ?? '-';
        document.getElementById('risk-impact').innerText = risk.impact ?? '-';
        document.getElementById('risk-mitigation').innerText = risk.mitigation_plan ?? '-';
        document.getElementById('risk-contingency').innerText = risk.contingency_plan ?? '-';
        document.getElementById('risk-owner').innerText = risk.risk_owner ?? '-';
        document.getElementById('risk-notes').innerText = risk.notes ?? '-';
        const probability = document.getElementById('risk-probability');
        const severity = document.getElementById('risk-severity');
        const status = document.getElementById('risk-status');

        setBadge(probability, risk.probability, {
            high: ['bg-red-100', 'text-red-700'],
            medium: ['bg-yellow-100', 'text-yellow-700'],
            low: ['bg-green-100', 'text-green-700']
        });

        setBadge(severity, risk.severity, {
            high: ['bg-red-100', 'text-red-700'],
            medium: ['bg-yellow-100', 'text-yellow-700'],
            low: ['bg-green-100', 'text-green-700']
        });

        setBadge(status, risk.status, {
            open: ['bg-blue-100', 'text-blue-700'],
            closed: ['bg-green-100', 'text-green-700']
        });

        const modal = document.getElementById('risk-detail-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeRiskDetail() {
        const modal = document.getElementById('risk-detail-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('risk-detail-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRiskDetail();
        }
    });

    function setBadge(element, value, colors) {
        value = value ?? '-';

        element.innerText = value.charAt(0).toUpperCase() + value.slice(1);

        element.className =
            "inline-flex px-3 py-1 rounded-full text-xs font-semibold";

        if (colors[value]) {
            element.classList.add(...colors[value]);
        } else {
            element.classList.add('bg-slate-100', 'text-slate-700');
        }
    }
</script>
