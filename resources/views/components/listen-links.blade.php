{{--
    Where to listen to a release: "Listen now" leads to the smartlink (every
    platform) or else Spotify. A direct Spotify link is added only next to a
    smartlink; otherwise both buttons would lead to the same page.
--}}
@props([
    'release',
])

@if (filled($release->listenUrl()))
    <div {{ $attributes->class('flex flex-wrap items-center gap-3') }}>
        <x-site.button :href="$release->listenUrl()" primary external>
            Listen now
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8" /></svg>
        </x-site.button>

        @if (filled($release->smartlink_url) && filled($release->spotify_url))
            <x-site.button :href="$release->spotify_url" external>
                <x-icons.spotify />
                Spotify
            </x-site.button>
        @endif
    </div>
@endif
