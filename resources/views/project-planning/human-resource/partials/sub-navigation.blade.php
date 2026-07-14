@php
    $tab = request('tab', 'hr');
@endphp

<div class="flex items-center gap-2 bg-slate-100 p-1 rounded-xl max-w-md">
    
    <a href="{{ route('projects.human-resource.show', $project->id) }}?tab=hr"
        class="flex-1 py-1.5 text-center text-xs font-bold rounded-lg transition
        {{ $tab === 'hr' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
        {{ __('Human Resource') }}
    </a>

    <a href="{{ route('projects.human-resource.show', $project->id) }}?tab=gantt"
        class="flex-1 py-1.5 text-center text-xs font-bold rounded-lg transition
        {{ $tab === 'gantt' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
        {{ __('Gantt Chart') }}
    </a>
</div>