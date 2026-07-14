<div class="bg-white border border-slate-300 rounded-2xl p-3 mt-2 cursor-grab" data-id="{{ $task->id }}">
    <!-- Header: Type & Priority -->
    <div class="flex gap-2 mb-4">
        <h2 class="bg-blueStatus w-fit border border-gradientBlue text-gradientBlue text-xs p-1 rounded-md">
            {{ $task->type ?? 'Feature' }}
        </h2>
        <h2 class="text-xs p-1 rounded-md
        @if($task->priority == 'low') bg-green-400 border border-green-700 text-green-900
        @elseif($task->priority == 'medium') bg-orangeStatus border border-gradientOrange text-gradientOrange
        @elseif($task->priority == 'high') bg-red-400 border border-red-700 text-red-900
        @else bg-gray-400
        @endif">
            {{ $task->priority ?? '-' }}
        </h2>
    </div>

    <!-- Title & Description -->
    <div class="border-b-2 pb-4 mb-4">
        <h3 class="text-sm text-slate-400">
            {{ $task->code ?? 'TASK-' . $task->id }}
        </h3>

        <h1 class="font-semibold text-lg font-lato mb-2">
            {{ $task->title }}
        </h1>

        <p class="text-xs text-slate-500">
            {{ $task->description }}
        </p>
    </div>

    <!-- Footer -->
    <div class="flex items-center justify-between">

        <!-- Assignees -->
        <div class="flex flex-col gap-2">
            @if($task->users && $task->users->count())
                @foreach ($task->users as $user)
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <span class="text-xs text-slate-600">
                            {{ $user->name }}
                        </span>
                    </div>
                @endforeach
            @else
                <span class="text-xs text-slate-400">
                    Belum ada PIC
                </span>
            @endif
        </div>

        <!-- Date -->
        <p class="text-sm text-slate-500">
            {{ $task->due_date
                ? \Carbon\Carbon::parse($task->due_date)->translatedFormat('d M Y')
                : $task->created_at->format('d M Y') 
                }}
        </p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.task-list').forEach(el => {
            new Sortable(el, {
                group: 'tasks',
                animation: 150,
                ghostClass: 'bg-gray-200',

                onAdd: function(evt) {
                    let taskId = evt.item.dataset.id
                    let newStatus = evt.to.dataset.status

                    console.log('Task ID:', taskId)
                    console.log('New Status:', newStatus)

                    updateTaskStatus(taskId, newStatus)
                }
            })
        })

    })

    function updateTaskStatus(taskId, status) {
        fetch(`/tasks/${taskId}/update-status`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    status
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log('SUCCESS:', data)
            })
            .catch(err => {
                console.error('ERROR:', err)
                alert('Gagal update status!')
            })
    }
</script>
