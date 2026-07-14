<div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col h-[500px] overflow-hidden">
    <!-- HEADER -->
    <div class="flex items-center justify-between px-5 py-4 bg-slate-50/50 border-b border-slate-100 flex-shrink-0">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full {{ $color }}"></span>
            <h3 class="text-sm font-semibold text-slate-700 capitalize">{{ $title }}</h3>
        </div>

        <span
            class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-full bg-white border border-slate-200 text-xs font-semibold text-slate-500 shadow-sm">
            {{ count($tasks) }}
        </span>
    </div>

    <!-- TASK LIST -->
    <div class="flex-1 overflow-y-auto p-4 space-y-3 task-list"
        data-status="{{ $status }}">

        @forelse ($tasks as $task)
            @include('project-executing.task-management.partials.task-card', ['task' => $task,])
        @empty

            <div class="flex flex-col items-center justify-center h-full text-slate-400">
                <i class="far fa-clipboard text-4xl mb-3"></i>
                <p class="text-sm font-medium">No Tasks</p>
                <p class="text-xs">Belum ada task pada kolom ini.</p>
            </div>
        @endforelse
    </div>
</div>