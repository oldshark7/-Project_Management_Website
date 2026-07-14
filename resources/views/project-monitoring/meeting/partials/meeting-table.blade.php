<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="h-[360px] overflow-y-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr
                    class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <td class="text-center py-2 w-14">No</td>
                    <td class="py-2">Meeting</td>
                    <td class="py-2">Project</td>
                    <td class="py-2">Date & Time</td>
                    <td class="py-2">Location</td>
                    <td class="py-2 text-center">Status</td>
                    <td class="py-2 text-center w-52">Action</td>
                </tr>
            </thead>

            <tbody>
                @forelse($meetings as $meeting)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                        <td class="text-center py-4">
                            {{ $loop->iteration }}
                        </td>

                        <td class="py-4">
                            <div class="font-semibold text-slate-700">
                                {{ $meeting->title }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $meeting->meeting_type }}
                            </div>
                        </td>

                        <td class="py-4">
                            {{ $meeting->project->name ?? '-' }}
                        </td>

                        <td class="py-4">
                            <div>
                                {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('d M Y') }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ substr($meeting->start_time, 0, 5) }}
                                @if ($meeting->end_time)
                                    - {{ substr($meeting->end_time, 0, 5) }}
                                @endif
                            </div>
                        </td>

                        <td class="py-4">
                            @if ($meeting->meeting_link)
                                <span class="text-blue-600 font-medium">
                                    Online Meeting
                                </span>
                            @elseif($meeting->location)
                                {{ $meeting->location }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="text-center">
                            @php
                                $statusColor = match ($meeting->status) {
                                    'Scheduled' => 'bg-blue-100 text-blue-700',
                                    'Completed' => 'bg-green-100 text-green-700',
                                    'Cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp

                            <span class="px-3 py-1 rounded-full text-[11px] font-semibold {{ $statusColor }}">
                                {{ $meeting->status }}
                            </span>
                        </td>

                        <td>
                            <div class="flex justify-center gap-2">
                                <!-- Detail -->
                                <button
                                    type="button"
                                    onclick='openDetailMeetingModal(@json($meeting))'
                                    class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- Edit -->
                                <button
                                    type="button"
                                    onclick='openEditMeetingModal(@json($meeting))'
                                    class="px-3 py-2 rounded-xl border border-amber-200 hover:bg-amber-50 text-amber-600">
                                    <i class="fas fa-pen"></i>
                                </button>

                                <!-- Delete -->
                                <button
                                    type="button"
                                    onclick="openDeleteMeetingModal({{ $meeting->id }}, '{{ addslashes($meeting->title) }}')"
                                    class="px-3 py-2 rounded-xl border border-red-200 hover:bg-red-50 text-red-600">
                                    <i class="fas fa-trash"></i>
                                </button>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            No meeting schedule found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
