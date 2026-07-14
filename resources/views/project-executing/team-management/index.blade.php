<x-app-layout>
    <x-slot name="header">
        <x-header-component/>
    </x-slot>

    @php
        if (!function_exists('getInitials')) {
            function getInitials($name)
            {
                $words = explode(' ', trim($name));
                $initials = '';
                foreach ($words as $w) {
                    $initials .= strtoupper(substr($w, 0, 1));
                    if (strlen($initials) >= 2) {
                        break;
                    }
                }
                return $initials ?: 'TM';
            }
        }
    @endphp

    <div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-full mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 Pb-5">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        {{ __('MANAGEMENT') }}</div>
                    <h2 class="font-semibold text-3xl">
                        {{ __('Manajemen Tim') }}
                    </h2>
                    <p class="text-sm text-slate-500"">
                        {{ __('Kelola kolaborator, peran, dan beban kerja tim Anda secara real-time.') }}
                    </p>
                </div>
                @if(in_array(strtolower(Auth::user()->role), ['pmo', 'project management officer']))
                <div>
                    <button type="button" onclick="openAddMemberModal()"
                        class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition gap-2">
                        <i class="fas fa-user-plus"></i>
                        {{ __('Tambah Anggota') }}
                    </button>
                </div>
                @endif
            </div>

            <!-- Alerts Container -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl text-xs flex items-center justify-between shadow-sm transition-all">
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-xs shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-exclamation-circle text-rose-500 text-sm"></i>
                            <span class="font-bold">{{ __('Terjadi Kesalahan Validasi') }}</span>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Summary Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Card 1: Total Anggota -->
                <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex items-center justify-between min-h-[110px] relative overflow-hidden">
                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">
                            {{ __('Total Anggota') }}
                        </span>
                        <h3 class="text-3xl font-extrabold text-slate-800 mt-2 tracking-tight">
                            {{ $totalMembers }}
                        </h3>
                        <div class="mt-2 text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                            <span>{{ __('Aktif di sistem') }}</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm shadow-sm border border-blue-100">
                        <i class="fas fa-user-friends"></i>
                    </div>
                </div>

                <!-- Card 2: Beban Rata-rata -->
                <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex items-center justify-between min-h-[110px] relative overflow-hidden">
                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">
                            {{ __('Beban Rata-rata') }}
                        </span>
                        <h3 class="text-3xl font-extrabold text-slate-800 mt-2 tracking-tight">
                            {{ $avgWorkload }}%
                        </h3>
                        <div class="mt-2 text-[10px] font-bold {{ $avgWorkload > 80 ? 'text-amber-600' : 'text-emerald-600' }} flex items-center gap-1">
                            <span>Status: {{ $avgWorkload > 80 ? __('Padat') : __('Produktif') }}</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-sm shadow-sm border border-purple-100">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>

                <!-- Card 3: Alokasi Tim Proyek -->
                <div class="bg-blue-600 rounded-2xl p-6 shadow-sm flex flex-col justify-between min-h-[110px] relative overflow-hidden text-white">
                    <div>
                        <span class="text-blue-100 text-[10px] font-bold uppercase tracking-wider block">
                            {{ __('Alokasi Tim Proyek') }}
                        </span>
                        <p class="text-[10px] text-blue-100 mt-1 font-semibold leading-relaxed">
                            {{ __('Distribusi beban kerja anggota tim yang terintegrasi langsung dengan perencanaan SDM.') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 mt-4 text-[10px] font-bold text-white/90">
                        <i class="fas fa-info-circle"></i>
                        <span>Workload otomatis terupdate dari HR Planning</span>
                    </div>
                </div>
            </div>
            
            <!-- member table -->
            @include('project-executing.team-management.partials.member-table')
        </div>
    </div>

    <!-- MODAL: ADD MEMBER -->
    <div id="add-member-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background Overlay -->
            <div class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" aria-hidden="true"
                onclick="closeAddMemberModal()"></div>
            <!-- Center Align -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl border border-slate-100 transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('teamManagement.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fas fa-user-plus text-blue-600"></i>
                                {{ __('Tambah Anggota Tim') }}
                            </h3>
                            <button type="button" onclick="closeAddMemberModal()"
                                class="text-slate-400 hover:text-slate-600 transition">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Nama Lengkap -->
                            <div>
                                <label for="member_name"
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Nama Lengkap') }}</label>
                                <input type="text" name="name" id="member_name" required value="{{ old('name') }}" placeholder="Contoh: John Doe"
                                    class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50">
                            </div>

                            <!-- Email Login -->
                            <div>
                                <label for="member_email"
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Email Login') }}</label>
                                <input type="email" name="email" id="member_email" required value="{{ old('email') }}" placeholder="Contoh: raka@psm.com"
                                    class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50">
                                <p class="text-[10px] text-slate-400 font-bold mt-1">
                                    {{ __('Akun login akan dibuat otomatis dengan role IT.') }}
                                </p>
                            </div>

                            <!-- Password & Konfirmasi Password -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="member_password"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Password Login') }}</label>
                                    <input type="password" name="password" id="member_password" required placeholder="Minimal 8 karakter"
                                        class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                </div>
                                <div>
                                    <label for="member_password_confirmation"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Konfirmasi Password') }}</label>
                                    <input type="password" name="password_confirmation" id="member_password_confirmation" required placeholder="Ulangi password"
                                        class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                </div>
                            </div>

                            <!-- Peran & Default Capacity -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="member_role"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Peran (Role)') }}</label>
                                    <input type="text" name="role_name" id="member_role" required value="{{ old('role_name') }}" placeholder="Contoh: Fullstack Developer"
                                        class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50">
                                </div>
                                <div>
                                    <label for="member_capacity"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Kapasitas Default (%)') }}</label>
                                    <input type="number" name="default_capacity_percentage" id="member_capacity" required value="{{ old('default_capacity_percentage', 100) }}" min="0" max="100"
                                        class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                </div>
                            </div>

                            <!-- Keahlian -->
                            <div>
                                <label for="member_skills"
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Keahlian (Pisahkan dengan koma)') }}</label>
                                <input type="text" name="skills" id="member_skills" required value="{{ old('skills') }}" placeholder="Contoh: PHP, Laravel, MySQL"
                                    class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50">
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label for="member_notes"
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Catatan / Notes') }}</label>
                                <textarea name="notes" id="member_notes" rows="2" placeholder="Informasi tambahan anggota..."
                                    class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 placeholder-slate-400 bg-slate-50/50">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" onclick="closeAddMemberModal()"
                            class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-700 rounded-xl text-xs font-bold transition">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            {{ __('Tambah') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: EDIT MEMBER -->
    <div id="edit-member-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background Overlay -->
            <div class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" aria-hidden="true"
                onclick="closeEditMemberModal()"></div>
            <!-- Center Align -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl border border-slate-100 transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="edit-member-form" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_member_id">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fas fa-edit text-blue-600"></i>
                                {{ __('Ubah Data Anggota') }}
                            </h3>
                            <button type="button" onclick="closeEditMemberModal()"
                                class="text-slate-400 hover:text-slate-600 transition">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Nama Lengkap -->
                            <div>
                                <label for="edit_member_name"
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Nama Lengkap') }}</label>
                                <input type="text" name="name" id="edit_member_name" required
                                    class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                            </div>

                            <!-- Peran & Default Capacity -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="edit_member_role_name"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Peran (Role)') }}</label>
                                    <input type="text" name="role_name" id="edit_member_role_name" required
                                        class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                </div>
                                <div>
                                    <label for="edit_member_capacity"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Kapasitas Default (%)') }}</label>
                                    <input type="number" name="default_capacity_percentage" id="edit_member_capacity" required min="0" max="100"
                                        class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                                </div>
                            </div>

                            <!-- Keahlian -->
                            <div>
                                <label for="edit_member_skills"
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Keahlian (Pisahkan dengan koma)') }}</label>
                                <input type="text" name="skills" id="edit_member_skills" required
                                    class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50">
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label for="edit_member_notes"
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Catatan / Notes') }}</label>
                                <textarea name="notes" id="edit_member_notes" rows="2"
                                    class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 bg-slate-50/50"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" onclick="closeEditMemberModal()"
                            class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-700 rounded-xl text-xs font-bold transition">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS script for filters and modals -->
    <script>
        function toggleDropdown(button) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== button.nextElementSibling) {
                    menu.classList.add('hidden');
                }
            });
            button.nextElementSibling.classList.toggle('hidden');
        }

        window.addEventListener('click', function(e) {
            if (!e.target.closest('.relative') || !e.target.closest('.fas.fa-ellipsis-v')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }
        });

        function filterMembers() {
            const search = document.getElementById('member-search').value.toLowerCase();
            const role = document.getElementById('role-filter').value.toLowerCase();
            const status = document.getElementById('status-filter').value.toLowerCase();

            const rows = document.querySelectorAll('.member-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const skills = row.getAttribute('data-skills').toLowerCase();
                const rowRole = row.getAttribute('data-role').toLowerCase();
                const rowStatus = row.getAttribute('data-status').toLowerCase();

                const matchesSearch = name.includes(search) || skills.includes(search) || rowRole.includes(search);
                const matchesRole = !role || rowRole === role;
                const matchesStatus = !status || rowStatus === status;

                if (matchesSearch && matchesRole && matchesStatus) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            document.getElementById('filtered-count').innerText = visibleCount;
        }

        function openAddMemberModal() {
            document.getElementById('add-member-modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeAddMemberModal() {
            document.getElementById('add-member-modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        @if ($errors->any() && old('email') !== null)
            window.addEventListener('DOMContentLoaded', (event) => {
                openAddMemberModal();
            });
        @endif

        function openEditMemberModal(member) {
            document.getElementById('edit_member_id').value = member.id;
            document.getElementById('edit_member_name').value = member.name;
            document.getElementById('edit_member_role_name').value = member.role_name;
            document.getElementById('edit_member_skills').value = member.skills;
            document.getElementById('edit_member_capacity').value = member.default_capacity_percentage;
            document.getElementById('edit_member_notes').value = member.notes || '';
            
            // Set form action dynamically
            const form = document.getElementById('edit-member-form');
            form.action = `/team-management/${member.id}`;
            
            document.getElementById('edit-member-modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeEditMemberModal() {
            document.getElementById('edit-member-modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
</x-app-layout>
