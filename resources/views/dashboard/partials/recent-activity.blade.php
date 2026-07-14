<div class="col-span-3 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between p-5 border-b border-slate-100">
        <h2 class="card-title text-black">{{ __('Recent Activitys') }}
        </h2>
        <a href="{{ route('projects.index') }}"
            class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 transition-all">
            <span>{{ __('Lihat Semua') }}</span>
            <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                    <th class="py-3.5 px-6 border-b border-slate-100">{{ __('Proyek') }}</th>
                    <th class="py-3.5 px-6 border-b border-slate-100">{{ __('Pengguna') }}</th>
                    <th class="py-3.5 px-6 border-b border-slate-100">{{ __('Aktivitas') }}</th>
                    <th class="py-3.5 px-6 border-b border-slate-100">{{ __('Waktu') }}</th>
                    <th class="py-3.5 px-6 border-b border-slate-100">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                @if ($recentProjects->isEmpty())
                    <!-- Fallback Mock Rows matching reference image exactly if no database records -->
                    <tr class="hover:bg-slate-50/30 transition-colors">
                        <td colspan="5" class="py-6 text-center text-slate-400 text-sm">
                            {{ __('No Data Found') }}
                        </td>
                    </tr>
                @else
                    <!-- Render Dynamic Database projects -->
                    @foreach ($recentProjects as $proj)
                        <tr>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">{{ $proj['title'] }}</div>
                                <div class="text-[10px] text-slate-450 mt-0.5">{{ $proj['category'] }}</div>
                            </td>

                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-7 h-7 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center font-bold text-blue-600 text-[10px]">
                                        {{ $proj['initials'] }}
                                    </div>
                                    <span class="font-semibold text-slate-700">{{ $proj['name'] }}</span>
                                </div>
                            </td>

                            <td class="py-4 px-6 text-slate-500">{{ $proj['activity'] }}</td>
                            <td class="py-4 px-6 text-slate-400">{{ $proj['time'] }}</td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 border rounded-full text-[10px] font-bold {{ $proj['status_class'] }}">
                                    {{ $proj['status_label'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>