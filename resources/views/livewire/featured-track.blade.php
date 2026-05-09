@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
@endphp

<div>
    @if ($track)
        <div class="flex items-center gap-4 max-w-md mx-auto px-4 py-3 rounded-lg bg-white/5 backdrop-blur border border-red-800/20 hover:bg-white/10 transition-colors">
            @if ($track->cover_image)
                <img
                    src="{{ asset('storage/'.$track->cover_image) }}"
                    alt="Cover for {{ $track->title }} by {{ $track->artist_name }}"
                    width="64"
                    height="64"
                    loading="lazy"
                    decoding="async"
                    class="flex-none w-16 h-16 rounded-md object-cover shadow-lg shadow-black/40"
                />
            @else
                <div
                    class="flex-none flex items-center justify-center w-16 h-16 rounded-md bg-gradient-to-br from-red-800 to-red-500 text-2xl font-bold text-white shadow-lg shadow-black/40"
                    aria-hidden="true"
                >
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($track->title, 0, 1)) }}
                </div>
            @endif

            <div class="flex-1 text-left min-w-0">
                <p class="flex items-center gap-2 text-xs font-semibold tracking-widest text-red-500 uppercase">
                    <span class="flex items-end gap-0.5 h-3" aria-hidden="true">
                        <span class="eq-bar eq-bar-1 w-0.5 bg-red-500"></span>
                        <span class="eq-bar eq-bar-2 w-0.5 bg-red-500"></span>
                        <span class="eq-bar eq-bar-3 w-0.5 bg-red-500"></span>
                    </span>
                    Now Spinning
                </p>
                <p class="mt-0.5 font-medium text-white truncate">
                    <span class="block sm:inline">{{ $track->title }}</span>
                    <span class="hidden sm:inline text-gray-500"> &mdash; </span>
                    <span class="block sm:inline text-gray-300">{{ $track->artist_name }}</span>
                </p>
                <a
                    href="{{ $track->spotify_track_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 mt-1 text-sm font-medium text-green-500 hover:text-green-400 transition-colors {{ $focusRing }}"
                    aria-label="Listen on Spotify — {{ $track->title }} by {{ $track->artist_name }}"
                >
                    <x-icons.spotify class="w-4 h-4" />
                    <span>Listen on Spotify</span>
                </a>
            </div>
        </div>
    @endif
</div>
