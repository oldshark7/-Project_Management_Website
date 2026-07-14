<table class="w-full">
    <thead>
        <tr class="text-sm text-slate-500 tracking-wide border-b-2">
            <th class="px-3 font-light text-left">{{ __('Project Name') }}</th>
            <th class="font-light text-left">{{ __('Task') }}</th>
            <th class="font-light">{{ __('Duedate') }}</th>
            <th class="font-light">{{ __('Status') }}</th>
            <th class="font-light">{{ __('Priority') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($assignedTask as $task)
            <tr onclick="window.location='{{ url('/task-management/' . $task->project_id) }}'" class="border-b hover:bg-slate-50 text-sm cursor-pointer">
                <td class="px-3 py-4 font-semibold">{{ $task->project_name }}</td>
                <td>{{ $task->task_name }}</td>
                <td class="text-center px-2">{{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}</td>
                <td class="text-center px-4">{{ ucfirst($task->kanban_status) }}</td>
                <td class="text-center px-4">
                    <span class="w-full inline-block py-1 rounded-md text-sm
                        @if ($task->priority == 'low') bg-green-300 border border-green-700 text-green-900
                        @elseif($task->priority == 'medium') bg-orangeStatus border border-gradientOrange text-gradientOrange
                        @elseif($task->priority == 'high') bg-red-400 border border-red-700 text-red-900
                        @else bg-gray-400 @endif">
                        {{ ucfirst($task->priority) }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    Tidak ada task.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>