<div class="mt-4 bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="px-6 pt-6 pb-2">
        <h1 class="text-lg font-semibold text-slate-800">
            Project Task List
        </h1>
    </div>

    {{-- Header --}}
    <table class="w-full text-left border-collapse">
        <thead>
            <tr
                class="bg-slate-50/50 border-y border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                <th class="px-6 py-4 w-16 text-center">No</th>
                <th class="px-6 py-4">Task</th>
                <th class="px-6 py-4">Description</th>
                <th class="px-6 py-4 w-32 text-center">Priority</th>
                <th class="px-6 py-4 w-40 text-center">Remaining</th>
                <th class="px-6 py-4 w-40 text-center">Due Date</th>
                <th class="px-6 py-4 w-48 text-center">Finished</th>
            </tr>
        </thead>
    </table>

    {{-- Body --}}
    <div class="max-h-[260px] overflow-y-auto">
        <table class="w-full text-left border-collapse">
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                @forelse ($tasks as $task)
                    <tr class="hover:bg-slate-50 transition duration-150 cursor-pointer"
                        onclick="openTaskModal({
                            id: {{ $task->id }},
                            title: @js($task->title),
                            description: @js($task->description ?? '-'),
                            priority: @js($task->priority ?? '-'),
                            due_date: @js(optional($task->timelineItem)->end_date?->format('d M Y') ?? '-'),
                            status: @js($task->kanban_status ?? '-')
                        })">

                        <td class="px-6 py-4 w-16 text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">
                                {{ $task->title }}
                            </div>

                            <div class="text-[10px] text-slate-400 mt-1">
                                Status : {{ ucfirst($task->kanban_status ?? '-') }}
                            </div>
                        </td>

                        <td class="px-6 py-4 max-w-sm truncate" title="{{ $task->description }}">
                            {{ $task->description ?? '-' }}
                        </td>

                        <td class="px-6 py-4 w-32 text-center">
                            @php
                                $priorityClass = match (strtolower($task->priority ?? '')) {
                                    'high' => 'bg-red-100 text-red-700',
                                    'medium' => 'bg-amber-100 text-amber-700',
                                    'low' => 'bg-emerald-100 text-emerald-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp

                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $priorityClass }}">
                                {{ ucfirst($task->priority ?? '-') }}
                            </span>
                        </td>

                        <td class="px-6 py-4 w-40 text-center">
                            @php
                                $dueDate = optional($task->timelineItem)->end_date;
                            @endphp

                            @if ($task->kanban_status == 'approved')
                                -
                            @elseif($dueDate)
                                {{ (int) now()->diffInDays($dueDate, false) }} hari
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-6 py-4 w-40 text-center whitespace-nowrap">
                            {{ optional($task->timelineItem)->end_date?->format('d M Y') ?? '-' }}
                        </td>

                        <td class="px-6 py-4 w-48 text-center">
                            @if ($task->kanban_status === 'approved')
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                    Approved
                                </span>

                                <div class="text-[10px] text-slate-400 mt-1">
                                    {{ $task->statusUpdater->name ?? '-' }}
                                </div>
                            @elseif($task->task_status_finished_at)
                                {{ $task->task_status_finished_at->format('d M Y') }}
                            @else
                                -
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="far fa-clipboard text-5xl mb-4"></i>
                                <p class="text-lg font-semibold text-slate-500">
                                    Tidak ada task
                                </p>
                                <p class="text-sm">
                                    Belum ada task yang ditugaskan kepada Anda.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>