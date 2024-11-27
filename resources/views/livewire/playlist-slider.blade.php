<div class="py-24 bg-black/95">
    <div class="container px-4 mx-auto">
        <!-- Section Header -->
        <div class="mb-16 text-center">
            <h2 class="mb-3 text-sm tracking-wider text-gray-400 uppercase">Playlists</h2>
            <p class="text-4xl font-bold text-red-800">Our Curated Collections</p>
        </div>

        <!-- Playlist Slider -->
        <div 
            x-data="{
                playing: false,
                init() {
                    // Autoplay handling
                    setInterval(() => {
                        if (!this.playing) {
                            $wire.nextSlide()
                        }
                    }, 5000)
                }
            }"
            class="relative"
        >
            <!-- Main Slider Container -->
            <div class="relative w-full max-w-4xl mx-auto">
                <!-- Slides Container -->
                <div class="relative aspect-[4/3] bg-gray-900/50 rounded-xl overflow-hidden">
                    @foreach($playlists as $index => $playlist)
                        <div 
                            x-show="$wire.currentIndex === {{ $index }}"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform translate-x-full"
                            x-transition:enter-end="opacity-100 transform translate-x-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 transform translate-x-0"
                            x-transition:leave-end="opacity-0 transform -translate-x-full"
                            class="absolute inset-0 w-full h-full"
                        >
                            <div class="w-full h-full p-4">
                                {!! $playlist->spotify_embed_url !!}
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Navigation Arrows -->
                <button 
                    @click="$wire.previousSlide()"
                    class="absolute left-0 p-3 text-white transition-colors duration-300 -translate-x-12 -translate-y-1/2 rounded-full top-1/2 bg-red-800/90 hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2 focus:ring-offset-black"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <button 
                    @click="$wire.nextSlide()"
                    class="absolute right-0 p-3 text-white transition-colors duration-300 translate-x-12 -translate-y-1/2 rounded-full top-1/2 bg-red-800/90 hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2 focus:ring-offset-black"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- Pagination Dots -->
            <div class="flex justify-center mt-8 space-x-2">
                @foreach($playlists as $index => $playlist)
                    <button 
                        wire:click="goToSlide({{ $index }})"
                        class="w-3 h-3 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-red-800 focus:ring-offset-2 focus:ring-offset-black {{ $currentIndex === $index ? 'bg-red-800' : 'bg-gray-600 hover:bg-red-800/50' }}"
                        aria-label="Go to slide {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        </div>
    </div>
</div>