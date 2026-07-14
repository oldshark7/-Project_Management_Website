<x-app-layout>
    <x-slot name="header">
        <x-header-component mode="issueRisk" />
    </x-slot>
    <div class="bg-white p-4 rounded-2xl border border-slate-100 h-full shadow-sm p-6 max-w-full mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <a href="{{ route('issue-risk.index') }}"
                        class="text-[10px] hover:text-slate-500 font-bold text-slate-400 uppercase tracking-widest">
                        <i class="fas fa-arrow-left text-[8px]"></i>
                        {{ __('Kembali | ') }}
                    </a>
                    {{ $tab === 'issue' ? 'ISSUE MANAGEMENT / ' . $project->title : 'RISK MANAGEMENT / ' . $project->title }}
                </div>

                <h1 class="font-semibold text-3xl">
                    {{ $tab === 'issue' ? 'Issue Management' : 'Risk Management' }}
                </h1>

                <p class="text-sm text-slate-500">
                    {{ $tab === 'issue' ? "Track all issues here, don't lose track!" : 'Monitor project risks and their impact.' }}
                </p>
            </div>
            @if ($tab === 'issue')
                <a onclick="openModal()"
                    class="px-4 py-1 bg-gradient-to-br from-blue-600 to-gradientBlue hover:bg-blue-700 text-white rounded-xl text-lg transition shadow-blue-500/10 hover:shadow-lg cursor-pointer">
                    <i class="fas fa-plus text-[10px]"></i>
                    Add Issue
                </a>
            @endif
        </div>

        <div class="flex items-center justify-between mb-6">
            @include('project-executing.issue-risk-management.partials.sub-navigation')
            <div class="w-20 block">
            </div>
            <div class="flex items-center justify-center gap-3">
                @include('project-executing.issue-risk-management.partials.filter')
            </div>
        </div>

        <!-- content here -->
        @if ($tab === 'issue')
            @if (!is_null($issues))
                @include('project-executing.issue-risk-management.partials.issue-table', [
                    'issues' => $issues,
                ])
            @else
                <div class="text-center py-32 text-slate-400">
                    <i class="fas fa-folder text-5xl"></i>
                    <p class="text-xl font-semibold">Belum ada project dipilih</p>
                    <p class="text-sm">Silakan pilih project melalui search di atas</p>
                </div>
            @endif
        @else
            @if (!is_null($issues))
                @if ($tab === 'risk' && $riskSuggestion)
                    <div id="ai-risk-assessment-box"
                        class="mb-6 rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-5">

                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center">
                                <i class="fas fa-robot"></i>
                            </div>

                            <div>
                                <h3 class="font-bold text-slate-800">
                                    AI Risk Assessment
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Berdasarkan Scope, Tasks, Issues, dan seluruh Risk Register.
                                </p>
                            </div>
                        </div>

                        <div id="ai-risk-summary" class="text-sm text-slate-700 leading-7 whitespace-pre-line">
                            {!! nl2br(e($riskSuggestion['summary'] ?? '-')) !!}
                        </div>
                    </div>

                    @if ($riskSuggestion['loading'] ?? false)
                        <script>
                            (function() {
                                const projectId = {{ $project->id }};
                                const pollUrl = `/risk-suggestion/${projectId}/status`;
                                const maxAttempts = 40; // 40 x 5s = ~3.3 menit max polling
                                let attempts = 0;

                                const poll = setInterval(async () => {
                                    attempts++;

                                    try {
                                        const res = await fetch(pollUrl, {
                                            headers: {
                                                'Accept': 'application/json'
                                            }
                                        });
                                        const data = await res.json();

                                        if (!data.loading) {
                                            clearInterval(poll);
                                            // Reload halaman supaya semua bagian (summary + predicted_risks) ke-render server-side
                                            window.location.reload();
                                        }
                                    } catch (err) {
                                        console.error('Polling AI risk suggestion gagal:', err);
                                    }

                                    if (attempts >= maxAttempts) {
                                        clearInterval(poll);
                                        const box = document.getElementById('ai-risk-summary');
                                        if (box) {
                                            box.innerHTML =
                                                'Analisis AI memakan waktu lebih lama dari biasanya. Silakan refresh halaman beberapa saat lagi.';
                                        }
                                    }
                                }, 5000);
                            })();
                        </script>
                    @endif
                @endif
                @include('project-executing.issue-risk-management.partials.risk-table', [
                    'issues' => $issues,
                ])
            @else
                <div class="text-center py-32 text-slate-400">
                    <i class="fas fa-folder text-5xl"></i>
                    <p class="text-xl font-semibold">Belum ada project dipilih</p>
                    <p class="text-sm">Silakan pilih project melalui search di atas</p>
                </div>
            @endif
        @endif
    </div>
    @include('project-executing.issue-risk-management.partials.issue-form')
    @include('project-executing.issue-risk-management.partials.issue-detail')
    @include('project-executing.issue-risk-management.partials.risk-detail')
</x-app-layout>
<script>
    document.getElementById('issue-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    function openModal() {
        document.getElementById('issue-modal').classList.remove('hidden');
        document.getElementById('issue-modal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('issue-modal').classList.add('hidden');
        document.getElementById('issue-modal').classList.remove('flex');
    }
</script>
