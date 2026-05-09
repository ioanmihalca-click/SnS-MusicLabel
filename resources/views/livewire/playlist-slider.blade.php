@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
@endphp

<div id="playlists" class="py-12 md:py-24 bg-gradient-to-b from-gray-900 via-black to-gray-900" data-reveal>
    <div class="container px-4 mx-auto">
        <x-section-header eyebrow="Playlists" title="Our Curated Collections" :gradient="true" class="mb-8 md:mb-12" />

        {{-- Curator credibility pill (moved from hero — semantically belongs next to playlists) --}}
        <div class="flex justify-center mb-10 md:mb-14">
            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 transition-colors">
                <x-icons.spotify class="w-5 h-5 text-green-500" />
                <span class="text-sm font-medium text-gray-200">
                    Tastemaker &amp; Curator <span class="text-gray-500">·</span> Spotify Playlists
                </span>
            </div>
        </div>

        <div class="relative max-w-4xl mx-auto">
            {{-- Slide stage: fixed height so absolute children compose predictably and the iframe can fill it. --}}
            <div class="relative h-[440px] md:h-[480px] overflow-hidden shadow-2xl shadow-black/40 bg-gray-900/70 rounded-2xl border border-white/5">
                {{-- Slim loading bar shown while a wire:click is in flight --}}
                <div wire:loading.delay class="absolute top-0 left-0 right-0 z-30 h-0.5 overflow-hidden bg-gray-800/40">
                    <div class="h-full bg-gradient-to-r from-red-700 to-red-500 animate-pulse"></div>
                </div>

                @foreach ($playlists as $index => $playlist)
                    <div
                        wire:key="playlist-slide-{{ $playlist->id }}"
                        class="absolute inset-0 p-4 transition-opacity duration-500 ease-out
                            {{ $currentIndex === $index ? 'opacity-100 z-10' : 'opacity-0 pointer-events-none z-0' }}"
                        @if ($currentIndex !== $index) aria-hidden="true" @endif
                    >
                        <div class="w-full h-full [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:rounded-lg">
                            {!! $playlist->spotify_embed_url !!}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col items-center mt-6 space-y-4">
                <div class="flex items-center space-x-6">
                    <button
                        wire:click="previousSlide"
                        type="button"
                        aria-label="Previous playlist"
                        class="p-3 text-white transition-transform transform bg-red-700 rounded-full shadow-lg hover:scale-110 {{ $focusRing }}"
                    >
                        <x-icons.chevron-left class="w-6 h-6" />
                    </button>

                    <button
                        wire:click="nextSlide"
                        type="button"
                        aria-label="Next playlist"
                        class="p-3 text-white transition-transform transform bg-red-700 rounded-full shadow-lg hover:scale-110 {{ $focusRing }}"
                    >
                        <x-icons.chevron-right class="w-6 h-6" />
                    </button>
                </div>

                <div class="flex items-center justify-center space-x-3">
                    @foreach ($playlists as $index => $playlist)
                        <button
                            wire:click="goToSlide({{ $index }})"
                            type="button"
                            class="w-3 h-3 rounded-full transition-transform transform {{ $focusRing }} {{ $currentIndex === $index ? 'bg-red-700 scale-125' : 'bg-gray-500 hover:bg-red-600' }}"
                            aria-label="Go to slide {{ $index + 1 }}"
                            @if ($currentIndex === $index) aria-current="true" @endif
                        ></button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
