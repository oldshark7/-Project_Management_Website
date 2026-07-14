<x-app-layout>
    <x-slot name="header">
        <x-header-component mode="costControl" />
    </x-slot>

    <div class="bg-white p-4 rounded-2xl border border-slate-100 min-h-full h-fit shadow-sm p-6 max-w-full mx-auto">
        <div class="mb-6 flex justify-between items-center border-b border-slate-100 pb-5">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <a href="{{ route('cost-control.index') }}"
                        class="text-[10px] hover:text-slate-500 font-bold text-slate-400 uppercase tracking-widest">
                        <i class="fas fa-arrow-left text-[8px]"></i>
                        {{ __('Kembali | ') }}
                    </a>
                    {{ __('COST CONTROL') . __(' / ') . __($project->title ?? '-') }}
                </div>
                <h1 class="font-semibold text-3xl">{{ __('Cost Control') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Monitoring your project budget here.') }}</p>
            </div>
        </div>

        <div class="flex gap-6 mb-4">
            <!-- Cost Breakdown -->
            <div class="flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-xl">
                        Cost Breakdown
                    </h3>

                    <button type="button" onclick="openAddModal()"
                        class="flex items-center justify-center px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl shadow-sm transition gap-1.5">
                        <i class="fas fa-plus text-xs"></i> Add Item
                    </button>
                </div>

                @include('project-monitoring.cost-control.partials.budget-table')
            </div>

            <!-- KPI -->
            <div class="w-80 shrink-0">
                <div class="grid gap-4">
                    <div
                        class="rounded-xl p-4 shadow-sm bg-gradient-to-br from-green-500 to-emerald-600 text-white hover:scale-[1.01] transition-all">
                        <p class="text-sm text-white/80">Remaining / Variance</p>
                        <h2 class="text-2xl font-black">
                            Rp {{ number_format($remaining, 0, ',', '.') }},-
                        </h2>
                    </div>

                    <div
                        class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm hover:scale-[1.01] transition-all">
                        <p class="text-sm text-slate-500">Planned Budget</p>
                        <h2 class="text-2xl font-bold">
                            Rp {{ number_format($planned, 0, ',', '.') }},-
                        </h2>
                    </div>

                    <div
                        class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm hover:scale-[1.01] transition-all">
                        <p class="text-sm text-slate-500">Actual Cost</p>
                        <h2 class="text-2xl font-bold">
                            Rp {{ number_format($actual, 0, ',', '.') }},-
                        </h2>
                    </div>

                    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm hover:scale-[1.01] transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-slate-500">Budget Usage</p>

                            <span
                                class="text-lg font-bold
                                {{ $usage > 100 ? 'text-red-500' : ($usage > 80 ? 'text-yellow-500' : 'text-green-500') }}">
                                {{ number_format($usage, 0) }}%
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-700
                                {{ $usage > 100 ? 'bg-red-500' : ($usage > 80 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                style="width: {{ min($usage, 100) }}%">
                            </div>
                        </div>

                        <div class="flex justify-between mt-2 text-[11px] text-slate-400 font-medium">
                            <span>0%</span>
                            <span>50%</span>
                            <span>100%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @include('project-monitoring.cost-control.partials.footer-ai-analysis')
    </div>
    @include('project-monitoring.cost-control.partials.add-item-modal')
</x-app-layout>
<script>
    function openAddModal() {
        const modal = document.getElementById('add-modal');

        modal.classList.remove('hidden');
    }

    function closeAddModal() {
        const modal = document.getElementById('add-modal');

        modal.classList.add('hidden');
    }
</script>
