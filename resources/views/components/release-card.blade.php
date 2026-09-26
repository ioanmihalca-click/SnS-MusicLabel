{{--
    A release in a catalogue grid: artwork, title (linked to the release page),
    credit and "date · format", with a Play button beside the title when
    Spotify can play the release. Expects the `artists` relation to be loaded.
--}}
@props([
    'release',
])

@php
    $url = route('releases.show', $release->slug);
    $coverUrl = $release->coverUrl();
    $meta = array_filter([$release->released_at?->format('d.m.Y'), $release->format?->getLabel()]);
@endphp

<article {{ $attributes->class('group flex min-w-0 flex-col gap-3.5') }}>
    <a href="{{ $url }}" wire:navigate tabindex="-1" aria-hidden="true" data-markdown-ignore class="relative block aspect-square overflow-hidden bg-slab">
        @if ($coverUrl)
            <img
                src="{{ $coverUrl }}"
                alt="Cover of {{ $release->title }}"
                width="640"
                height="640"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(.2,.7,.1,1)] group-hover:scale-[1.04]"
            >
        @else
            <span class="grid h-full w-full place-items-center">
                <x-icons.logomark class="h-1/4 w-1/4 text-rule2" />
            </span>
        @endif
    </a>

    <div class="flex min-w-0 items-start gap-3">
        <div class="flex min-w-0 flex-1 flex-col gap-[3px]">
            <a href="{{ $url }}" wire:navigate class="text-[16.5px] font-semibold leading-tight [overflow-wrap:anywhere] hover:underline hover:underline-offset-[3px]">{{ $release->title }}</a>

            @if (filled($release->credit))
                <span class="text-sm text-mist">{{ $release->credit }}</span>
            @endif

            @if ($meta !== [])
                <span class="mt-1.5 font-meta text-[10.5px] uppercase tracking-[.08em] text-dim">{{ implode(' · ', $meta) }}</span>
            @endif
        </div>

        <x-play-button :uri="$release->playUri()" :title="$release->title" :credit="$release->credit" :url="$url" />
    </div>
</article>
