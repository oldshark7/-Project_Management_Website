<div class="mt-2 h-[260px] overflow-y-auto">
    <table class="w-full">
        <!-- header -->
        <thead class="sticky top-0 bg-white z-10">
            <tr class="text-slate-400 text-sm border-b">
                <td class="w-40">PIC / Nama</td>
                <td class="text-center w-24">Total Task</td>
                <td class="text-center w-24">Done</td>
                <td class="text-center">Progress Bar</td>
            </tr>
        </thead>

        <!-- content -->
        <tbody>
            @foreach ($memberStats as $member)
            <tr>
                <td class="py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr($member['name'], 0, 2)) }}
                        </div>
                        <div>
                            <h1 class="font-semibold">{{ $member['name'] }}</h1>
                            <p class="text-xs text-slate-400">{{ $member['role'] }}</p>
                        </div>
                    </div>
                </td>

                <td class="text-center">{{ $member['total'] }}</td>
                <td class="text-center">{{ $member['done'] }}</td>

                <td>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div 
                            class="bg-green-500 h-2 rounded-full"
                            style="width: {{ $member['progress'] }}%">
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $member['progress'] }}%
                    </p>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>