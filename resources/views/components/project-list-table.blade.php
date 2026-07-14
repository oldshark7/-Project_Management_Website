@props(['projects', 'route', 'buttonText' => 'Lihat Proyek'])

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="h-full overflow-y-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr
                    class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <th class="px-6 py-4 w-16 text-center">No</th>
                    <th class="px-6 py-4">Judul Proyek</th>
                    <th class="px-6 py-4">Pemilik</th>
                    <th class="px-6 py-4">Deadline Proyek</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                @forelse ($projects as $project)
                    <tr class="hover:bg-slate-50 transition duration-150">
                        <td class="px-6 py-4 text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-6 py-4 font-medium text-slate-800">
                            {{ $project->title }}
                            <div class="text-[10px] text-slate-400 font-semibold mt-1 flex items-center gap-1.5">
                                <i class="far fa-calendar-alt text-slate-300"></i>
                                {{ __('Mulai: ') . ($project->start_date ? $project->start_date->format('d M Y') : '-') }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-full bg-[#0B1329] text-white flex items-center justify-center font-black text-[9px] shadow-sm uppercase tracking-wider">
                                    {{ strtoupper(substr($project->owner ? $project->owner->name : 'PM', 0, 2)) }}
                                </div>
                                {{ $project->owner->name ?? '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            <a href="{{ route($route, $project) }}"
                                class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl shadow-sm transition gap-1.5">
                                {{ $buttonText }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            Belum ada data proyek.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
