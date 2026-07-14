<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
    <div>
        <h2 class="card-title text-black mb-6">{{ __('Next Action') }}</h2>
        <div class="flex flex-col gap-5">
            @if (empty($nextActions))
                <div class="mt-6">
                    <h1 class="mb-2 text-slate-500 font-semibold flex flex-col items-center justify-center gap-2"><i class="fas fa-file-alt text-slate-400 text-4xl"></i>No Action for Now!</h1>
                    <p class="text-sm text-slate-500 text-justify">You're all caught up! When there's something important, it'll appear here.</p>
                </div>
            @else
                @foreach (array_slice($nextActions, 0, 3) as $act)
                    <div class="flex items-start gap-4">
                        <!-- Icon circles -->
                        @if ($act['color'] === 'rose')
                            <div
                                class="bg-rose-50 text-rose-500 border border-rose-100 p-2.5 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas {{ $act['icon'] }} text-xs"></i>
                            </div>
                        @elseif($act['color'] === 'blue')
                            <div
                                class="bg-blue-50 text-blue-500 border border-blue-100 p-2.5 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas {{ $act['icon'] }} text-xs"></i>
                            </div>
                        @elseif($act['color'] === 'emerald')
                            <div
                                class="bg-emerald-50 text-emerald-500 border border-emerald-100 p-2.5 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas {{ $act['icon'] }} text-xs"></i>
                            </div>
                        @else
                            <div
                                class="bg-indigo-50 text-indigo-500 border border-indigo-100 p-2.5 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas {{ $act['icon'] }} text-xs"></i>
                            </div>
                        @endif

                        <div>
                            <h3 class="text-xs font-bold text-slate-850 leading-snug line-clamp-1"
                                title="{{ $act['title'] }}">{{ $act['title'] }}</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $act['subtext'] }}</p>
                            <a href="{{ $act['link'] }}"
                                class="text-[10px] font-bold text-blue-600 hover:text-blue-800 mt-2 inline-block transition-colors hover:underline">
                                {{ $act['action_text'] }}
                            </a>
                        </div>
                    </div>

                    @if (!$loop->last)
                        <hr class="border-slate-50">
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>