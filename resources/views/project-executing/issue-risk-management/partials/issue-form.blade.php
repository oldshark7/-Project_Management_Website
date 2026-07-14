<div id="issue-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-lg p-6 relative">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Create Issue</h2>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form action="{{ route('issues.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- hidden project_id -->
            <input type="hidden" name="project_id" value="{{ $project->id }}">

            <div>
                <label class="text-xs text-slate-500">Title</label>
                <input type="text" name="title" required class="w-full mt-1 px-3 py-2 border rounded-lg text-sm focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="text-xs text-slate-500">Description</label>
                <textarea name="description" required class="w-full mt-1 px-3 py-2 border rounded-lg text-sm"></textarea>
            </div>

            <div>
                <label class="text-xs text-slate-500">Assignee</label>
                <select name="assignee_id" required class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                    <option value="">-</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs text-slate-500">Priority</label>
                <select name="priority" required class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div>
                <label class="text-xs text-slate-500">Due Date</label>
                <input type="date" name="due_date" min="{{ now()->toDateString() }}" required class="w-full mt-1 px-3 py-2 border rounded-lg text-sm">
            </div>

            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 text-sm rounded-lg bg-slate-100">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm rounded-lg bg-blue-600 text-white">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>