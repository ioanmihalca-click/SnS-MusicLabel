<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Parallax Background -->
    <div 
        class="absolute inset-0 bg-cover bg-center bg-no-repeat transform scale-105" 
        style="background-image: url('/assets/img/music-bg.jpg');"
        x-data
        @scroll.window="$el.style.transform = `scale(1.05) translateY(${window.pageYOffset * 0.3}px)`"
    >
        <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/40 to-black"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_transparent_0%,_#000000_100%)] opacity-80"></div>
    </div>

    <div class="container relative px-4 mx-auto z-10 pt-20">
        <div class="max-w-5xl mx-auto text-center" x-data="{
            init() {
                setTimeout(() => this.showContent = true, 500);
            },
            showContent: false
        }">
            
            <!-- Main Content Container -->
            <div 
                class="relative transition-all duration-1000 transform"
                :class="showContent ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'"
            >
                <!-- Badge -->
                <div class="inline-flex items-center px-4 py-2 mb-8 space-x-2 rounded-full backdrop-blur-md"
                     style="background: linear-gradient(145deg, rgba(20, 20, 20, 0.8), rgba(10, 10, 10, 0.9)); border: 1px solid rgba(75, 75, 75, 0.15);">
                    <span class="flex w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-bold tracking-widest text-white uppercase font-display">Music Management & Label</span>
                </div>

                <!-- Massive Headline -->
                <h1 class="mb-6 text-6xl font-bold font-display sm:text-7xl md:text-8xl lg:text-9xl tracking-tighter text-white">
                    SNOW N <br class="md:hidden" /> 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-200 to-gray-500">STUFF</span>
                </h1>

                <!-- Subheadline -->
                <div class="mb-12 space-y-4">
                    <p class="text-xl md:text-2xl font-light text-gray-400 max-w-3xl mx-auto leading-relaxed">
                        Redefining the sound of 
                        <span class="font-medium text-white">Tech House</span>, 
                        <span class="font-medium text-white">Deep House</span>, & 
                        <span class="font-medium text-white">Techno</span>.
                    </p>
                </div>

                <!-- Management Tags -->
                <div class="flex flex-wrap justify-center gap-3 mb-12">
                    @foreach(['THK', 'G&S', 'Snow N Stuff', 'Style Da Kid'] as $artist)
                        <span class="px-4 py-2 text-sm font-bold text-gray-300 uppercase transition-all duration-300 rounded-xl backdrop-blur-sm"
                              style="
                                  background: linear-gradient(145deg, rgba(20, 20, 20, 0.8), rgba(10, 10, 10, 0.9));
                                  border: 1px solid rgba(75, 75, 75, 0.12);
                              ">
                            {{ $artist }}
                        </span>
                    @endforeach
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col items-center justify-center gap-5 sm:flex-row">
                    <a href="#releases" 
                       class="group relative px-8 py-4 overflow-hidden rounded-xl text-white font-bold transition-all duration-300 hover:scale-105"
                       style="
                           background: linear-gradient(135deg, rgba(220, 38, 38, 0.95) 0%, rgba(127, 29, 29, 0.95) 100%);
                           box-shadow: 0 8px 30px -10px rgba(220, 38, 38, 0.5);
                       ">
                        <span class="relative z-10 flex items-center">
                            Explore Releases
                            <svg class="w-5 h-5 ml-2 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </span>
                    </a>
                    
                    <a href="#artists" 
                       class="group px-8 py-4 rounded-xl text-white font-bold transition-all duration-300 hover:scale-105 backdrop-blur-sm"
                       style="
                           background: linear-gradient(145deg, rgba(30, 30, 30, 0.8), rgba(20, 20, 20, 0.9));
                           border: 1px solid rgba(75, 75, 75, 0.15);
                           box-shadow: 0 4px 15px -5px rgba(0, 0, 0, 0.3);
                       ">
                        Discover Artists
                    </a>
                </div>

                <!-- Spotify Curator Badge -->
                <div class="mt-16 inline-flex items-center space-x-4 px-6 py-4 rounded-2xl backdrop-blur-md transition-all duration-300 cursor-default"
                     style="
                         background: linear-gradient(145deg, rgba(20, 20, 20, 0.85), rgba(10, 10, 10, 0.9));
                         border: 1px solid rgba(75, 75, 75, 0.12);
                     ">
                    <div class="p-2.5 rounded-xl"
                         style="background: linear-gradient(135deg, rgba(29, 185, 84, 0.15) 0%, rgba(29, 185, 84, 0.05) 100%); border: 1px solid rgba(29, 185, 84, 0.2);">
                        <svg class="w-6 h-6 text-[#1DB954]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <p class="text-xs text-gray-500 uppercase tracking-widest font-bold">Verified Curator</p>
                        <p class="text-sm font-medium text-white">Featuring on Spotify Playlists</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Down Indicator -->
    <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
        <div class="p-2 rounded-full" style="background: linear-gradient(145deg, rgba(30, 30, 30, 0.6), rgba(20, 20, 20, 0.7)); border: 1px solid rgba(75, 75, 75, 0.1);">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </div>
</section>
