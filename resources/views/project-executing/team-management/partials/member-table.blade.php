<!-- Main Filter & Search Workspace Card -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <!-- Filters Segment -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fas fa-search text-slate-400 text-xs"></i>
                </span>
                <input type="text" id="member-search" oninput="filterMembers()" placeholder="Cari anggota tim..."
                    class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 rounded-xl text-xs font-semibold text-slate-700 placeholder-slate-400">
            </div>

            @php
                $uniqueRoles = $teamMembers->pluck('role_name')->unique();
            @endphp
            <select id="role-filter" onchange="filterMembers()"
                class="text-xs font-bold text-slate-600 border border-slate-200 rounded-xl px-3 py-1.5 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20">
                <option value="">Semua Peran</option>
                @foreach ($uniqueRoles as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>

            <select id="status-filter" onchange="filterMembers()"
                class="text-xs font-bold text-slate-600 border border-slate-200 rounded-xl px-3 py-1.5 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20">
                <option value="">Semua Status Workload</option>
                <option value="Available">Available (0-50%)</option>
                <option value="Partially Allocated">Partially Allocated (51-80%)</option>
                <option value="Nearly Full">Nearly Full (81-99%)</option>
                <option value="Full">Full (>=100%)</option>
            </select>
        </div>
        <div class="text-xs font-bold text-slate-400">
            Menampilkan <span id="filtered-count" class="text-slate-700">{{ $teamMembers->count() }}</span> dari <span
                class="text-slate-700">{{ $totalMembers }}</span> anggota
        </div>
    </div>

    <!-- Member list table -->
    <div class="overflow-x-auto -mx-6">
        <div class="inline-block min-w-full align-middle px-6">
            <table class="min-w-full text-left divide-y divide-slate-50">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3">{{ __('ANGGOTA') }}</th>
                        <th class="py-3 px-3">{{ __('PERAN & STATUS') }}</th>
                        <th class="py-3 px-3">{{ __('KEAHLIAN') }}</th>
                        <th class="py-3 px-3">{{ __('WORKLOAD & KAPASITAS') }}</th>
                        @if(in_array(strtolower(Auth::user()->role), ['pmo', 'project management officer']))
                        <th class="py-3 text-right pr-4">{{ __('AKSI') }}</th>
                        @endif
                    </tr>
                </thead>

                <tbody id="member-table-body" class="divide-y divide-slate-50 text-xs">
                    @forelse ($teamMembers as $member)
                        <tr class="member-row hover:bg-slate-50/30 transition duration-150"
                            data-name="{{ $member->name }}"
                            data-skills="{{ $member->skills }}"
                            data-role="{{ $member->role_name }}"
                            data-status="{{ $member->workload_status }}">
                            <!-- Member name & email -->
                            <td class="py-4 pr-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 font-extrabold flex items-center justify-center text-xs shadow-sm border border-blue-100">
                                            {{ getInitials($member->name) }}
                                        </div>
                                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 {{ $member->is_active ? 'bg-emerald-500' : 'bg-slate-300' }} border-2 border-white rounded-full"></span>
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                                            <span>{{ $member->name }}</span>
                                        </div>
                                        @if($member->notes && $member->notes !== '-')
                                            <div class="text-[10px] text-slate-400 font-medium italic mt-0.5 max-w-[250px] truncate" title="{{ $member->notes }}">{{ $member->notes }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Peran & Aktif Status -->
                            <td class="py-4 px-3">
                                <div class="flex flex-col gap-1.5 items-start">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-[#E0F2FE] text-[#0284C7] border border-[#BAE6FD]">
                                        {{ $member->role_name }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[8px] font-bold {{ $member->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-650 border border-slate-200' }}">
                                        {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Keahlian -->
                            <td class="py-4 px-3 max-w-[220px]">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $skillsArr = array_filter(array_map('trim', explode(',', $member->skills)));
                                    @endphp
                                    @forelse ($skillsArr as $skill)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border bg-slate-50 text-slate-600 border-slate-200">
                                            {{ $skill }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 italic text-[10px]">-</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Workload Progress -->
                            <td class="py-4 px-3">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-3">
                                        <div class="w-24 bg-slate-100 rounded-full h-2 overflow-hidden shrink-0">
                                            @php
                                                $barColor = 'bg-emerald-500';
                                                $textColor = 'text-emerald-600';
                                                $wPercent = $member->current_workload_percentage;
                                                
                                                if ($wPercent >= 100) {
                                                    $barColor = 'bg-rose-500';
                                                    $textColor = 'text-rose-600';
                                                } elseif ($wPercent > 80) {
                                                    $barColor = 'bg-amber-500';
                                                    $textColor = 'text-amber-600';
                                                } elseif ($wPercent > 50) {
                                                    $barColor = 'bg-blue-500';
                                                    $textColor = 'text-blue-600';
                                                }
                                            @endphp
                                            <div class="h-full rounded-full {{ $barColor }}" style="width: {{ min(100, $wPercent) }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-500 font-mono text-[10px]">{{ $wPercent }}%</span>
                                        <span class="font-bold {{ $textColor }} text-[10px]">{{ $member->workload_status }}</span>
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-bold">
                                        Kapasitas: {{ $member->default_capacity_percentage }}% (Sisa: {{ $member->remaining_capacity_percentage }}%)
                                    </div>
                                </div>
                            </td>

                            <!-- Aksi Dropdown -->
                            @if(in_array(strtolower(Auth::user()->role), ['pmo', 'project management officer']))
                            <td class="py-4 text-right pr-4 relative">
                                <div class="inline-block text-left">
                                    <button type="button" onclick="toggleDropdown(this)"
                                        class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-50 transition">
                                        <i class="fas fa-ellipsis-v text-xs"></i>
                                    </button>
                                    <!-- Dropdown menu -->
                                    <div class="dropdown-menu hidden absolute right-4 mt-1 w-36 bg-white border border-slate-100 rounded-xl shadow-lg z-20 py-1 font-bold text-slate-600 text-[10px] text-left">
                                        <button type="button" 
                                            onclick='openEditMemberModal({!! json_encode($member) !!})'
                                            class="w-full text-left block px-4 py-2 hover:bg-slate-50 hover:text-slate-800 transition">
                                            Ubah Data
                                        </button>
                                        <form action="{{ route('teamManagement.toggleStatus', $member->id) }}" method="POST" class="w-full">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-full text-left block px-4 py-2 hover:bg-slate-50 hover:text-slate-800 transition">
                                                {{ $member->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('teamManagement.destroy', $member->id) }}" method="POST" class="w-full border-t border-slate-50" onsubmit="return confirm('Apakah Anda yakin ingin menghapus anggota tim ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left block px-4 py-2 hover:bg-slate-50 hover:text-rose-600 transition">
                                                Hapus Anggota
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array(strtolower(Auth::user()->role), ['pmo', 'project management officer']) ? 5 : 4 }}" class="py-8 text-center text-slate-400 italic font-semibold">
                                Belum ada data anggota tim. Klik "Tambah Anggota" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>