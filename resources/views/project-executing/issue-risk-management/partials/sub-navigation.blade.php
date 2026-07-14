<div class="flex items-center gap-2 bg-slate-100 p-1 rounded-xl max-w-md">

    <a href="{{ route('issue-risk.show', ['project' => $project->id, 'tab' => 'issue']) }}"
        class="flex-1 px-4 py-1.5 text-center text-xs font-bold rounded-lg transition
        {{ $tab === 'issue'
            ? 'bg-white text-slate-800 shadow-sm'
            : 'text-slate-500 hover:text-slate-700' }}">
        Issue
    </a>

    <a href="{{ route('issue-risk.show', ['project' => $project->id, 'tab' => 'risk']) }}"
        class="flex-1 px-4 py-1.5 text-center text-xs font-bold rounded-lg transition
        {{ $tab === 'risk'
            ? 'bg-white text-slate-800 shadow-sm'
            : 'text-slate-500 hover:text-slate-700' }}">
        Risk
    </a>

</div>