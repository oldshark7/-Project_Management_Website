<div class="grid grid-cols-4 w-full h-[500px] gap-4">

    @include('project-executing.task-management.partials.task-column', [
        'title' => 'To-Do',
        'status' => 'todo',
        'color' => 'bg-black',
        'tasks' => $allTasks['todo'] ?? collect()
    ])

    @include('project-executing.task-management.partials.task-column', [
        'title' => 'On-Going',
        'status' => 'ongoing',
        'color' => 'bg-purple',
        'tasks' => $allTasks['ongoing'] ?? collect()
    ])

    @include('project-executing.task-management.partials.task-column', [
        'title' => 'Done',
        'status' => 'done',
        'color' => 'bg-important',
        'tasks' => $allTasks['done'] ?? collect()
    ])

    @include('project-executing.task-management.partials.task-column', [
        'title' => 'Approved',
        'status' => 'approved',
        'color' => 'bg-blueStatus',
        'tasks' => $allTasks['approved'] ?? collect()
    ])

</div>