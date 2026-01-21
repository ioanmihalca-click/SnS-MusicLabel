<div id="playlists" class="py-20 md:py-32 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-black">
        <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(10, 10, 10, 1) 0%, rgba(20, 20, 20, 0.8) 50%, rgba(10, 10, 10, 1) 100%);"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full blur-[200px] animate-pulse-soft" 
             style="background: radial-gradient(circle, rgba(127, 29, 29, 0.1) 0%, transparent 70%);"></div>
    </div>

    <div class="container relative px-4 mx-auto z-10">
        <!-- Section Header -->
        <div class="mb-12 md:mb-20 text-center">
            <span class="badge-primary mb-4">
                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
                Playlists
            </span>
            <h2 class="text-4xl md:text-5xl font-bold font-display">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-red-500 to-red-800">Our Curated Collections</span>
            </h2>
        </div>

        <div 
            x-data="{ playing: false }"
            class="relative max-w-4xl mx-auto"
        >
            <!-- Main Player Card -->
            <div class="relative p-[1px] rounded-3xl"
                 style="background: linear-gradient(145deg, rgba(60, 60, 60, 0.2), rgba(30, 30, 30, 0.1));">
                
                <div class="relative overflow-hidden rounded-3xl"
                     style="
                        background: linear-gradient(145deg, rgba(20, 20, 20, 0.95), rgba(10, 10, 10, 0.98));
                        border: 1px solid rgba(75, 75, 75, 0.15);
                        box-shadow: 
                            0 25px 60px -15px rgba(0, 0, 0, 0.6),
                            0 10px 25px -10px rgba(0, 0, 0, 0.4),
                            inset 0 1px 0 0 rgba(255, 255, 255, 0.03);
                     ">
                    
                    <!-- Playlist Container -->
                    <div class="min-h-96 relative">
                        @foreach($playlists as $index => $playlist)
                            <div 
                                class="absolute inset-0 p-5"
                                style="display: {{ $currentIndex === $index ? 'block' : 'none' }}"
                            >
                                <div class="w-full h-full rounded-xl overflow-hidden"
                                     style="box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);">
                                    {!! $playlist->spotify_embed_url !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="flex flex-col items-center mt-8 space-y-6">
                <!-- Navigation Buttons -->
                <div class="flex items-center space-x-8">
                    <button 
                        wire:click="previousSlide"
                        class="p-4 rounded-xl transition-all duration-300 transform hover:scale-110 hover:-translate-x-1 focus:outline-none group"
                        style="
                            background: linear-gradient(135deg, rgba(220, 38, 38, 0.9) 0%, rgba(127, 29, 29, 0.95) 100%);
                            box-shadow: 0 4px 20px -5px rgba(220, 38, 38, 0.4);
                        "
                    >
                        <svg class="w-5 h-5 text-white transition-transform duration-300 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <button 
                        wire:click="nextSlide"
                        class="p-4 rounded-xl transition-all duration-300 transform hover:scale-110 hover:translate-x-1 focus:outline-none group"
                        style="
                            background: linear-gradient(135deg, rgba(220, 38, 38, 0.9) 0%, rgba(127, 29, 29, 0.95) 100%);
                            box-shadow: 0 4px 20px -5px rgba(220, 38, 38, 0.4);
                        "
                    >
                        <svg class="w-5 h-5 text-white transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <!-- Pagination Dots -->
                <div class="flex items-center justify-center gap-3">
                    @foreach($playlists as $index => $playlist)
                        <button 
                            wire:click="goToSlide({{ $index }})"
                            class="w-2.5 h-2.5 rounded-full focus:outline-none transition-all duration-300 transform {{ $currentIndex === $index ? 'scale-125' : 'hover:scale-110' }}"
                            style="
                                background: {{ $currentIndex === $index 
                                    ? 'linear-gradient(135deg, #dc2626 0%, #991b1b 100%)' 
                                    : 'rgba(255, 255, 255, 0.2)' }};
                                box-shadow: {{ $currentIndex === $index 
                                    ? '0 0 10px rgba(220, 38, 38, 0.5)' 
                                    : 'none' }};
                            "
                            aria-label="Go to slide {{ $index + 1 }}"
                        ></button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>