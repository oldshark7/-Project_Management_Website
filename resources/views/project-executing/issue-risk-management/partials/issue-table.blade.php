<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="h-full overflow-y-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr
                    class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <th class="px-6 py-4 w-32 text-center">Status</th>
                    <th class="px-6 py-4 w-40 text-center">Key</th>
                    <th class="px-6 py-4 w-64">Title</th>
                    <th class="px-6 py-4">Summary</th>
                    <th class="px-6 py-4 w-52">Assigned</th>
                    <th class="px-6 py-4 w-52">Reported By</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                @forelse($issues as $issue)
                    <tr class="hover:bg-slate-50 transition duration-150 cursor-pointer"
                        onclick='openDetailModal(@json($issue))'>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusClass = match (strtolower($issue->status)) {
                                    'open' => 'bg-red-100 text-red-700',
                                    'in progress' => 'bg-amber-100 text-amber-700',
                                    'resolved' => 'bg-emerald-100 text-emerald-700',
                                    'closed' => 'bg-slate-200 text-slate-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($issue->status) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center font-medium text-slate-500">
                            ISS-{{ str_pad($issue->id, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">
                                {{ $issue->title }}
                            </div>

                            @if ($issue->priority)
                                <div class="text-[10px] text-slate-400 mt-1">
                                    Priority : {{ ucfirst($issue->priority) }}
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-4 max-w-sm truncate" title="{{ $issue->description }}">
                            {{ $issue->description }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-full bg-[#0B1329] text-white flex items-center justify-center font-black text-[9px] uppercase tracking-wider">
                                    {{ strtoupper(substr($issue->assignee->name ?? 'NA', 0, 2)) }}
                                </div>
                                {{ $issue->assignee->name ?? '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-full bg-slate-600 text-white flex items-center justify-center font-black text-[9px] uppercase tracking-wider">
                                    {{ strtoupper(substr($issue->reporter->name ?? 'NA', 0, 2)) }}
                                </div>
                                {{ $issue->reporter->name ?? '-' }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="far fa-folder-open text-5xl mb-4"></i>
                                <p class="text-lg font-semibold text-slate-500">
                                    Belum ada issue
                                </p>

                                <p class="text-sm">
                                    Silakan membuat issue terlebih dahulu.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
    let currentIssueId = null;

    function openDetailModal(issue) {
        currentIssueId = issue.id;

        document.getElementById('detail-title').textContent = issue.title ?? '-';
        document.getElementById('detail-description').textContent = issue.description ?? '-';
        document.getElementById('detail-status').value = issue.status ?? 'open';
        document.getElementById('detail-priority').textContent = issue.priority ?? '-';
        document.getElementById('detail-assignee').textContent = issue.assignee?.name ?? '-';
        document.getElementById('detail-reporter').textContent = issue.reporter?.name ?? '-';
        document.getElementById('detail-due').textContent = issue.due_date ?? '-';

        document
            .getElementById('issue-detail-modal')
            .classList.remove('hidden');
    }

    function closeDetailModal() {
        document
            .getElementById('issue-detail-modal')
            .classList.add('hidden');
    }

    // klik luar modal = close
    document.getElementById('issue-detail-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDetailModal();
        }
    });
</script>
