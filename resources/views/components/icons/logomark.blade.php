@props(['title' => "Snow 'n' Stuff"])

@php
    $ariaHidden = $attributes->get('aria-hidden', 'true');
    $isDecorative = $ariaHidden === 'true';
@endphp

{{-- Hexagonal SnS monogram. Two interlocked S forms inside a regular hexagon. --}}
<svg
    {{ $attributes->merge(['class' => 'w-7 h-7', 'viewBox' => '0 0 64 64', 'fill' => 'none', 'aria-hidden' => $ariaHidden]) }}
    xmlns="http://www.w3.org/2000/svg"
>
    @unless ($isDecorative)
        <title>{{ $title }}</title>
    @endunless

    {{-- Hexagon frame --}}
    <path
        d="M32 3 L57 17 L57 47 L32 61 L7 47 L7 17 Z"
        stroke="currentColor"
        stroke-width="2.5"
        stroke-linejoin="round"
        opacity="0.9"
    />

    {{-- Top S (rotated counter-clockwise, sits to the left) --}}
    <path
        d="M26 22 C20 22 18 26 22 28 L28 30 C32 31 30 35 24 35"
        stroke="currentColor"
        stroke-width="3"
        stroke-linecap="round"
        stroke-linejoin="round"
        fill="none"
    />

    {{-- Bottom S (rotated 180°, sits to the right, interlocked) --}}
    <path
        d="M38 42 C44 42 46 38 42 36 L36 34 C32 33 34 29 40 29"
        stroke="currentColor"
        stroke-width="3"
        stroke-linecap="round"
        stroke-linejoin="round"
        fill="none"
    />

    {{-- Center dot for visual anchor --}}
    <circle cx="32" cy="32" r="1.5" fill="currentColor" />
</svg>
