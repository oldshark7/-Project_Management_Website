@props([
    'label',
    'value',
    'valueColor' => 'black',
    'titleColor' => 'black',
    'infoColor' => 'text-slate-400',
    'background' => 'white',
    'route' => '#'
])


<div class="border border-slate-200 {{ $background }} 
            rounded-2xl p-5 shadow-sm hover:scale-[1.01] transition-all duration-300 
            flex flex-col justify-between">
    <!-- title section -->
    <div class="flex items-center justify-between">
        <span class="card-title text-{{ $titleColor }}">{{ $label }}</span>
        @if ($route && Auth::check() && in_array(strtolower(Auth::user()->role), ['project management officer', 'pm', 'manager']))
            <button class="flex border border-slate-400 items-center justify-center rounded-full bg-white w-8 h-8" onClick="window.location.href=' {{ $route }}'">
                <i class="text-xs text-slate-500 fa-solid fa-arrow-up-right-from-square"></i>
            </button>        
        @endif
    </div>

    <!-- status value-->
    <div class="flex items-baseline justify-between mt-2 mb-2 ms-2">
        <span class="text-4xl font-black text-{{ $valueColor }} tracking-tight">{{ $value }}</span>
    </div>

    <!-- progress of the content value -->
    <!-- change to dynamic data if fix -->
    <div class="flex gap-3 items-center text-sm">
        <p class="{{ $infoColor }} border rounded-md px-2 flex items-center justify-center gap-1">
            0<i class="mt-0.5 fas fa-caret-up"></i>
        </p>
        <p class="{{ $infoColor }}">No project done this month</p>
    </div>
</div>