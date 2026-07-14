<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="h-[360px] overflow-y-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr
                    class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <td class="text-left py-2 px-2 w-14 text-center">No</td>
                    <td class="text-left py-2">Category</td>
                    <td class="text-left py-2">Description</td>
                    <td class="text-right pr-5">Budget</td>
                    <td class="text-right pr-5">Actual</td>
                    <td class="text-right pr-5">Variance</td>
                    <td class="text-right pr-5">Expanse</td>
                </tr>
            </thead>

            <tbody>
                @forelse($breakdown as $data)
                    <tr class="border-b border-slate-100">
                        <td class="py-4 px-2 text-center capitalize">
                            {{ $loop->iteration }}
                        </td>
                        <td class="py-4 w-36 capitalize">
                            {{ str_replace('_', ' ', $data['category']) }}
                        </td>
                        <td class="py-4 capitalize">
                            {{ str_replace('_', ' ', $data['description'] ) }}
                        </td>
                        <td class="w-42 pr-5 text-right">
                            Rp {{ number_format($data['planned'], 0, ',', '.') }}
                        </td>
                        <td class="w-42 pr-5 text-right">
                            Rp {{ number_format($data['actual'], 0, ',', '.') }}
                        </td>
                        <td class="w-42 pr-5 text-right {{ $data['variance'] < 0 ? 'text-red-500' : 'text-green-500' }}">
                            Rp {{ number_format($data['variance'], 0, ',', '.') }}
                        </td>
                        <td class="w-42 text-right pr-4">
                            <div class="flex justify-end">
                                @php
                                    $expenseItem = [
                                        'id' => $data['id'],
                                        'category' => str_replace('_',' ',$data['category']),
                                        'description' => $data['description'],
                                        'planned' => $data['planned'],
                                        'actual' => $data['actual'],
                                    ];
                                @endphp
                                <button type="button" onclick='openExpenseModal(@json($expenseItem))'
                                    class="flex items-center justify-center px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl shadow-sm transition gap-1.5">
                                    <i class="fas fa-receipt text-xs"></i>
                                    Record Expense
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-slate-400">
                            Tidak ada data cost
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('project-monitoring.cost-control.partials.expense-modal')
</div>
<script>
    let plannedCost = 0;
    let currentActual = 0;

    function openExpenseModal(item) {
        plannedCost = Number(item.planned);
        currentActual = Number(item.actual);

        document.getElementById('expense-budget-item-id').value = item.id;
        document.getElementById('expense-category').textContent =item.category;
        document.getElementById('expense-description').textContent =item.description;
        document.getElementById('expense-planned-cost').textContent =formatRupiah(plannedCost);
        document.getElementById('expense-current-actual').textContent =formatRupiah(currentActual);
        document.getElementById('expense-current-remaining').textContent =formatRupiah(plannedCost - currentActual);
        document.getElementById('expense-preview-actual').textContent =formatRupiah(currentActual);
        document.getElementById('expense-preview-remaining').textContent =formatRupiah(plannedCost - currentActual);
        document.getElementById('expense-amount').value = "";
        document.getElementById('expense-status').textContent = "Within Budget";
        document.getElementById('expense-status').className ="text-[10px] font-bold text-green-600";
        document.getElementById('expense-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeExpenseModal() {
        document.getElementById('expense-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('expense-amount').addEventListener('input', function () {
        const amount = Number(this.value) || 0;
        const previewActual = currentActual + amount;
        const previewRemaining = plannedCost - previewActual;

        document.getElementById('expense-preview-actual').textContent =formatRupiah(previewActual);
        document.getElementById('expense-preview-remaining').textContent =formatRupiah(previewRemaining);
        const remainingEl = document.getElementById('expense-preview-remaining');
        const statusEl = document.getElementById('expense-status');

        if (previewRemaining < 0) {
            remainingEl.classList.remove("text-green-600");
            remainingEl.classList.add("text-red-500");
            statusEl.textContent = "Over Budget";
            statusEl.className = "text-[10px] font-bold text-red-600";
        } else {
            remainingEl.classList.remove("text-red-500");
            remainingEl.classList.add("text-green-600");
            statusEl.textContent = "Within Budget";
            statusEl.className = "text-[10px] font-bold text-green-600";
        }
    });

    function formatRupiah(value) {
        return "Rp " + Number(value).toLocaleString("id-ID");
    }
</script>
