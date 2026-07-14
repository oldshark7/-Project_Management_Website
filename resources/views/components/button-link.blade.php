@props([
    'buttonTitle' => null,
    'url' => null
])

<button onclick="window.location.href='{{ $url }}'" class="flex items-center justify-center gap-2 w-full border broder-slate-400 rounded-2xl py-2 font-semibold text-blue-600">
    {{ $buttonTitle }} <i class="fas fa-arrow-right"></i>
</button>