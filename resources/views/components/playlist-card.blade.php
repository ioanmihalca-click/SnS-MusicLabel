{{--
    A Spotify playlist curated by the label: cover, title, tags and a Follow
    link. `headingLevel` follows the page's outline (h2 on /playlists, h3 on
    the homepage).
--}}
@props([
    'playlist',
    'headingLevel' => 'h2',
])

@php
    $coverUrl = $playlist->coverUrl();
@endphp

<article {{ $attributes->class('flex min-w-0 flex-col gap-3') }}>
    <div data-markdown-ignore class="aspect-square overflow-hidden bg-slab">
        @if ($coverUrl)
            <img
                src="{{ $coverUrl }}"
                alt="Cover of the {{ $playlist->displayTitle() }} playlist"
                width="300"
                height="300"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover"
            >
        @else
            <span class="grid h-full w-full place-items-center">
                <x-icons.spotify class="h-1/4 w-1/4 text-rule2" />
            </span>
        @endif
    </div>

    <{{ $headingLevel }} class="m-0 text-base font-semibold leading-tight">{{ $playlist->displayTitle() }}</{{ $headingLevel }}>

    @if (filled($playlist->description))
        <p class="m-0 font-meta text-[10px] uppercase leading-[1.7] tracking-[.08em] text-dim">{{ $playlist->description }}</p>
    @endif

    @if (filled($playlist->spotify_url))
        <div>
            <a
                href="{{ $playlist->spotify_url }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-[7px] rounded-full border border-rule2 bg-ink/35 px-[13px] py-[9px] text-[13px] font-medium leading-none transition-colors hover:border-frost"
            >
                <x-icons.spotify class="h-[15px] w-[15px]" />
                Follow<span class="sr-only"> {{ $playlist->displayTitle() }} on Spotify</span>
            </a>
        </div>
    @endif
</article>
