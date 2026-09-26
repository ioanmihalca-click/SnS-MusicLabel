{{--
    An artist in a roster grid: black-and-white portrait (colour on hover),
    name and role, linked to the artist page.
--}}
@props([
    'artist',
])

@php
    $photoUrl = $artist->photoUrl();
@endphp

<a
    href="{{ route('artists.show', $artist->slug) }}"
    wire:navigate
    {{ $attributes->class('group relative block aspect-[4/5] max-w-full overflow-hidden bg-slab text-frost') }}
>
    @if ($photoUrl)
        <img
            src="{{ $photoUrl }}"
            alt=""
            data-markdown-ignore
            loading="lazy"
            decoding="async"
            class="h-full w-full object-cover brightness-[.78] contrast-[1.15] grayscale transition duration-700 ease-[cubic-bezier(.2,.7,.1,1)] group-hover:scale-[1.03] group-hover:brightness-90 group-hover:contrast-[1.05] group-hover:grayscale-0 group-focus-visible:grayscale-0"
        >
    @else
        <span data-markdown-ignore class="grid h-full w-full place-items-center">
            <x-icons.logomark class="h-1/4 w-1/4 text-rule2" />
        </span>
    @endif

    <span aria-hidden="true" data-markdown-ignore class="absolute inset-0 bg-gradient-to-b from-ink/0 from-45% to-ink/[.92]"></span>

    <span class="absolute inset-x-0 bottom-0 z-[2] flex flex-col gap-2 p-[18px]">
        <span class="font-display text-[clamp(2rem,3.7vw,3.3rem)] font-black uppercase leading-[.84] [overflow-wrap:anywhere]">{{ $artist->name }}</span>
        @if (filled($artist->role))
            <span class="font-meta text-[10px] uppercase tracking-[.08em] text-mist">{{ $artist->role }}</span>
        @endif
    </span>
</a>
