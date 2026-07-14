<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">

    <div class="h-full overflow-y-auto">
        <table class="w-full text-left border-collapse">

            <thead>
                <tr
                    class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <th class="px-6 py-4 w-16 text-center">No</th>
                    <th class="px-6 py-4">Task</th>
                    <th class="px-6 py-4">Before</th>
                    <th class="px-6 py-4">After</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4">Requested By</th>
                    <th class="px-6 py-4 text-center whitespace-nowrap">Tanggal</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">

                @forelse ($changeRequests as $index => $cr)

                    <tr class="hover:bg-slate-50 transition duration-150 cursor-pointer"
                        onclick="openChangeRequestDetail({
                            title: @js($cr->wbsItem->title ?? '-'),
                            old_value: @js($cr->old_value ?? '-'),
                            new_value: @js($cr->new_value ?? '-'),
                            reason: @js($cr->reason ?? '-'),
                            status: @js($cr->status ?? '-'),
                            requested_by: @js($cr->requestedBy->name ?? '-'),
                            requested_deadline: @js($cr->requested_deadline ? \Carbon\Carbon::parse($cr->requested_deadline)->format('d M Y') : '-'),
                            date: @js($cr->created_at?->format('d M Y') ?? '-')
                        })">

                        <td class="px-6 py-4 text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">
                                {{ $cr->wbsItem->title ?? '-' }}
                            </div>

                            <div class="text-[10px] text-slate-400 mt-1">
                                <i class="far fa-calendar-alt text-slate-300 mr-1.5"></i>
                                Deadline :
                                {{ $cr->requested_deadline ? \Carbon\Carbon::parse($cr->requested_deadline)->format('d M Y') : '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-4 max-w-[180px] truncate text-slate-500"
                            title="{{ $cr->old_value }}">
                            {{ $cr->old_value ?? '-' }}
                        </td>

                        <td class="px-6 py-4 max-w-[180px] truncate"
                            title="{{ $cr->new_value }}">
                            {{ $cr->new_value ?? '-' }}
                        </td>

                        <td class="px-6 py-4 text-center">

                            @php
                                $statusClass = match($cr->status){
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'approved' => 'bg-emerald-100 text-emerald-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp

                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($cr->status) }}
                            </span>

                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">

                                <div
                                    class="w-7 h-7 rounded-full bg-[#0B1329] text-white flex items-center justify-center font-black text-[9px] uppercase tracking-wider">

                                    {{ strtoupper(substr($cr->requestedBy->name ?? 'PM',0,2)) }}

                                </div>

                                {{ $cr->requestedBy->name ?? '-' }}

                            </div>
                        </td>

                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            {{ $cr->created_at?->format('d M Y') ?? '-' }}
                        </td>

                        <td class="px-6 py-4 text-center">

                            @if (Auth::check() && strtolower(Auth::user()->role) === 'project management officer' && $cr->status == 'pending')

                                <div class="flex justify-center gap-2">

                                    <form action="{{ route('change-request.approve', $cr->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Approve change request ini?')">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition">
                                            Approve
                                        </button>

                                    </form>

                                    <form action="{{ route('change-request.reject', $cr->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Reject change request ini?')">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition">
                                            Reject
                                        </button>

                                    </form>

                                </div>

                            @else

                                <span class="text-slate-300">-</span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            Belum ada change request untuk project ini.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
    </div>
</div>