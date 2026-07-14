@props(['active'])

@php
$classes = ($active ?? false)
    ? 'flex w-full items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all bg-[#1E293B] text-white shadow-sm'
    : 'flex w-full items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all text-slate-400 hover:bg-[#1E293B]/70 hover:text-white';
@endphp

<div class="flex items-center">

    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
</div>
