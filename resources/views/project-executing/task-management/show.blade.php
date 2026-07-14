<x-app-layout>
    <x-slot name="header">
        <x-header-component mode="task" />
    </x-slot>

    <div class="bg-white p-4 rounded-2xl border border-slate-100 min-h-full h-fit shadow-sm p-6 max-w-full mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <a href="{{ route('task-management.index') }}"
                        class="text-[10px] hover:text-slate-500 font-bold text-slate-400 uppercase tracking-widest">
                        <i class="fas fa-arrow-left text-[8px]"></i>
                        {{ __('Kembali | ') }}
                    </a>
                    {{ __('TASK MANAGEMENT') . __(' / ') . __($project->title ?? '-') }}
                </div>
                <h1 class="font-semibold text-3xl">{{ __('Manajamen Task') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Manage all your project task with us!') }}</p>
            </div>
            <!-- dropdown filter-->
            @include('project-executing.task-management.partials.filter')
        </div>

        @if (empty($project))
            <div class="text-center py-32 text-slate-400">
                <i class="fas fa-folder text-5xl"></i>
                <p class="text-xl font-semibold">Belum ada project dipilih</p>
                <p class="text-sm">Silakan pilih project melalui search di atas</p>
            </div>
        @else
            <div class="flex flex-col gap-1">
                <div>@include('project-executing.task-management.partials.kanban-board', ['tasks' => $allTasks->groupBy('status'),])</div>
                <div>@include('project-executing.task-management.partials.task-list', ['tasks' => $allTasksRaw,])</div>
            </div>
        @endif
    </div>
    @include('project-executing.task-management.partials.task-detail-modal')
    @include('project-executing.task-management.partials.change-request-modal')
</x-app-layout>

<script>
    const projectId = {{ $project->id ?? 'null' }};
    function showTaskInsight() {
        if (!projectId) return;

        fetch(`/task-insight/${projectId}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) {console.log(res.message);return;}

                const toast = document.createElement('div');
                toast.className = `
                    fixed bottom-4 right-4 
                    bg-white shadow-lg 
                    p-4 rounded-lg w-96 
                    border-l-4 border-red-500 
                    z-50
                `;

                toast.innerHTML = `
                    <div class="font-semibold text-sm mb-1">Task Insight Summary</div>
                    <p class="text-sm text-slate-700 text-justify">${res.message}</p>
                    <button onclick="this.parentElement.remove()" class="text-xs mt-3 text-blue-500 hover:underline">Tutup</button>
                `;

                document.body.appendChild(toast);

                // optional: auto close after 10s
                setTimeout(() => {toast.remove();}, 100000);

            })
            .catch(err => {console.error('Task insight error:', err);});
    }

    window.addEventListener('DOMContentLoaded', () => {
        showTaskInsight();
    });

    window.openTaskModal = function(task) {
        console.log('modal open', task);
        window.currentTask = task;

        const modal = document.getElementById('taskDetailModal');

        document.getElementById('modalTaskTitle').innerText = task.title ?? '-';
        document.getElementById('modalTaskDesc').innerText = task.description ?? '-';
        document.getElementById('modalTaskPriority').innerText = task.priority ?? '-';
        document.getElementById('modalTaskDue').innerText = task.due_date ?? '-';
        document.getElementById('modalTaskStatus').innerText = task.status ?? '-';

        modal.classList.remove('hidden');
    };

    function closeTaskModal() {
        const modal = document.getElementById('taskDetailModal');
        modal.classList.add('hidden');
    }

    window.openChangeRequest = function() {
        const task = window.currentTask;

        if (!task) return;

        // isi data ke modal
        document.getElementById('cr_task_title').innerText = task.title ?? '-';

        // optional: auto isi current state dari desc
        document.getElementById('cr_current_state').value = task.description ?? '';

        // reset field lain
        document.getElementById('cr_proposed_state').value = '';
        document.getElementById('cr_reason').value = '';
        document.getElementById('cr_impact').value = 'medium';

        // buka modal
        document.getElementById('changeRequestModal').classList.remove('hidden');
    }

    function closeChangeRequestModal() {
        document.getElementById('changeRequestModal').classList.add('hidden');
    }

    function submitChangeRequest() {
        const deadline = document.getElementById('cr_deadline').value;

        if (!deadline) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Requested Deadline wajib diisi.'
            });
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const selectedDate = new Date(deadline);
        selectedDate.setHours(0, 0, 0, 0);

        if (selectedDate < today) {
            Swal.fire({
                icon: 'error',
                title: 'Tanggal Tidak Valid',
                text: 'Requested Deadline tidak boleh lebih kecil dari tanggal hari ini.'
            });
            return;
        }

        const payload = {
            wbs_item_id: window.currentTask?.id,
            old_value: document.getElementById('cr_current_state').value,
            new_value: document.getElementById('cr_proposed_state').value,
            reason: document.getElementById('cr_reason').value,
            requested_deadline: deadline,
            field_changed: 'flow',
        };

        fetch("{{ route('change-requests.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // <-- TAMBAHIN INI
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => {
                        throw err;
                    });
                }
                return res.json();
            })
            .then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Change Request berhasil dikirim.',
                        confirmButtonColor: '#0f172a'
                    });
                    closeChangeRequestModal();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message ?? 'Gagal mengirim Change Request.',
                        confirmButtonColor: '#dc2626'
                    });

                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: err.message ?? 'Gagal submit. Silakan coba lagi.',
                    confirmButtonColor: '#dc2626'
                });

            });
    }
</script>
