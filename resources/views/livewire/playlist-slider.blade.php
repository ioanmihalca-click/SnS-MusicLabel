@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
@endphp

<div id="playlists" class="py-12 md:py-24 bg-gradient-to-b from-gray-900 via-black to-gray-900" data-reveal>
    <div class="container px-4 mx-auto">
        <x-section-header eyebrow="Playlists" title="Our Curated Collections" :gradient="true" class="mb-8 md:mb-16" />

        <div
            x-data="{ playing: false }"
            class="relative max-w-4xl mx-auto"
        >
            <div class="relative">
                <div class="relative overflow-hidden shadow-lg bg-gray-900/70 rounded-2xl">
                    {{-- Slim loading bar shown while a wire:click is in flight --}}
                    <div wire:loading.delay class="absolute top-0 left-0 right-0 z-20 h-0.5 overflow-hidden bg-gray-800/40">
                        <div class="h-full bg-gradient-to-r from-red-700 to-red-500 animate-pulse"></div>
                    </div>
                    <div class="min-h-96">
                        @foreach($playlists as $index => $playlist)
                            <div
                                class="absolute inset-0 p-4"
                                style="display: {{ $currentIndex === $index ? 'block' : 'none' }}"
                            >
                                <div class="w-full h-full">
                                    {!! $playlist->spotify_embed_url !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center mt-4 space-y-4">
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
                    @foreach($playlists as $index => $playlist)
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
