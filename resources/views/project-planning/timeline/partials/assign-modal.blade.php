<!-- modal -->
<div id="assign-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeAssignModal()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">

            <form id="assign-form" method="POST">
                @csrf

                <div class="bg-white px-6 pt-6 pb-4">

                    <!-- HEADER -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-user-plus text-slate-900"></i>
                            Assign Anggota Tim
                        </h3>

                        <button type="button" onclick="closeAssignModal()"
                            class="text-slate-400 hover:text-slate-600 transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- CONTENT -->
                    <div id="member-container" class="space-y-4">
                        <div class="member-item border border-slate-200 rounded-xl p-4 bg-slate-50">
                            <div class="flex gap-2 items-center">
                                <select name="team_member_ids[]" class="w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100">

                                    @foreach ($teamMembers as $member)
                                        <option value="{{ $member->id }}">
                                            {{ $member->name }} - {{ $member->role_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="button" onclick="removeMember(this)" class="text-red-500 hover:text-red-700 transition">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <input type="number" name="workloads[]" required min="0" max="100" placeholder="Workload (%)" class="mt-3 w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100">
                        </div>
                    </div>

                    <button type="button" onclick="addMemberDropdown()" class="mt-4 text-xs font-semibold text-blue-600 hover:text-blue-700">
                        + Tambah Member
                    </button>
                </div>

                <!-- FOOTER -->
                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeAssignModal()" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-100 rounded-xl text-xs font-bold transition">
                        Batal
                    </button>

                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm">
                        Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentWbsId = null;

    document.getElementById('assign-form').addEventListener('submit', function(e) {
        const selects = document.querySelectorAll('select[name="team_member_ids[]"]');
        const ids = [];

        for (const select of selects) {
            if (ids.includes(select.value)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Member tidak boleh dipilih lebih dari satu kali.'
                });
                return;
            }

            ids.push(select.value);
        }

        const workloads = document.querySelectorAll('input[name="workloads[]"]');
        let total = 0;

        workloads.forEach(w => total += Number(w.value || 0));

        if (total > 90) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Total workload tidak boleh lebih dari 90%.'
            });
            return;
        }
    });

    function openAssignModal(wbsId) {
        currentWbsId = wbsId;
        const form = document.getElementById('assign-form');
        form.action = `/projects/{{ $project->id }}/assign/${wbsId}`;
        document.getElementById('assign-modal').classList.remove('hidden');
    }

    function closeAssignModal() {
        document.getElementById('assign-modal').classList.add('hidden');
    }

    function removeMember(btn) {
        const container = document.getElementById('member-container');

        if (container.children.length > 1) {
            btn.closest('.member-item').remove();
        } else {
            alert('Minimal 1 member harus dipilih');
        }
    }

    function addMemberDropdown() {
        const container = document.getElementById('member-container');

        const wrapper = document.createElement('div');
        wrapper.className = "member-item border border-slate-200 rounded-xl p-4 bg-slate-50";

        // === ROW SELECT + REMOVE ===
        const row = document.createElement('div');
        row.className = "flex gap-2 items-center";

        const select = document.createElement('select');
        select.name = "team_member_ids[]";
        select.className = "w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100";

        @foreach ($teamMembers as $member)
            const opt{{ $member->id }} = document.createElement('option');
            opt{{ $member->id }}.value = "{{ $member->id }}";
            opt{{ $member->id }}.text = "{{ $member->name }} - {{ $member->role_name }}";
            select.appendChild(opt{{ $member->id }});
        @endforeach

        const removeBtn = document.createElement('button');
        removeBtn.type = "button";
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.className ="text-red-500 hover:text-red-700 transition";
        removeBtn.onclick = function() {removeMember(removeBtn);};

        row.appendChild(select);
        row.appendChild(removeBtn);

        // === WORKLOAD INPUT ===
        const workload = document.createElement('input');
        workload.type = "number";
        workload.name = "workloads[]";
        workload.placeholder = "Workload (%)";
        workload.className = "mt-3 w-full text-xs rounded-xl border-slate-200 shadow-sm focus:border-slate-800 focus:ring focus:ring-slate-100";
        workload.min = 0;
        workload.max = 100;

        // === APPEND ===
        wrapper.appendChild(row);
        wrapper.appendChild(workload);

        container.appendChild(wrapper);
    }
</script>
