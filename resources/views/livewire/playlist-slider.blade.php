<div id="playlists" class="py-12 md:py-24 bg-gradient-to-b from-gray-900 via-black to-gray-900">
    <div class="container px-4 mx-auto">
        <div class="mb-8 text-center md:mb-16">
            <h2 class="mb-3 text-sm tracking-wider text-gray-400 uppercase">Playlists</h2>
            <p class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-red-800 to-red-500">
                Our Curated Collections
            </p>
        </div>

        <div 
            x-data="{ playing: false }"
            class="relative max-w-4xl mx-auto"
        >
            <div class="relative">
                <div class="relative overflow-hidden shadow-lg bg-gray-900/70 rounded-2xl">
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
                        class="p-3 text-white transition-transform transform bg-red-700 rounded-full shadow-lg hover:scale-110 focus:outline-none focus:ring-4 focus:ring-red-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <button 
                        wire:click="nextSlide"
                        class="p-3 text-white transition-transform transform bg-red-700 rounded-full shadow-lg hover:scale-110 focus:outline-none focus:ring-4 focus:ring-red-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center justify-center space-x-3">
                    @foreach($playlists as $index => $playlist)
                        <button 
                            wire:click="goToSlide({{ $index }})"
                            class="w-3 h-3 rounded-full focus:outline-none transition-transform transform focus:ring-4 {{ $currentIndex === $index ? 'bg-red-700 scale-125' : 'bg-gray-500 hover:bg-red-600' }}"
                            aria-label="Go to slide {{ $index + 1 }}"
                        ></button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>