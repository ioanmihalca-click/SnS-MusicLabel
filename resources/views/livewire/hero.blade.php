<section class="relative min-h-screen overflow-hidden pt-14 md:pt-20">
    <div class="absolute inset-0 bg-fixed bg-center bg-cover" style="background-image: url('/assets/img/music-bg.jpg')">
        <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/70 to-black/90"></div>
    </div>

    <div class="container relative px-4 pt-24 mx-auto">
        <div class="max-w-4xl mx-auto overflow-hidden text-center" x-data="{
            init() {
                    this.showWelcome = false;
                    this.showBrand = false;
                    this.showSubtitle = false;
                    this.showDesc = false;
                    this.showButtons = false;
        
                    setTimeout(() => this.showWelcome = true, 500);
                    setTimeout(() => this.showBrand = true, 1000);
                    setTimeout(() => this.showSubtitle = true, 1500);
                    setTimeout(() => this.showDesc = true, 2000);
                    setTimeout(() => this.showButtons = true, 2500);
                },
                showWelcome: false,
                showBrand: false,
                showSubtitle: false,
                showDesc: false,
                showButtons: false
        }">
            <div class="border rounded-lg shadow-lg bg-black/20 border-red-800/20 shadow-red-900/10">
                <!-- Main Title -->
                <div class="flex flex-wrap items-center justify-center text-5xl font-bold md:text-7xl gap-x-4">
                    <!-- Welcome to - cu animație mai pronunțată -->
                    <div class="transition-all duration-1000 transform"
                        :style="showWelcome ?
                            'opacity: 1; transform: translateX(0) scale(1);' :
                            'opacity: 0; transform: translateX(-100px) scale(0.8);'">
                        <span class="inline-block">Welcome to</span>
                    </div>

                    <!-- Snow n Stuff -->
                    <div class="text-transparent transition-all duration-1000 transform bg-clip-text bg-gradient-to-r from-red-800 to-red-500 animate-gradient"
                        :style="showBrand ?
                            'opacity: 1; transform: scale(1);' :
                            'opacity: 0; transform: scale(0.5);'">
                        Snow n Stuff
                    </div>
                </div>

                <!-- Subtitle -->
                <h2 class="mt-6 text-2xl font-light text-gray-300 transition-all duration-1000 transform md:text-3xl"
                    :style="showSubtitle ?
                        'opacity: 1; transform: translateY(0);' :
                        'opacity: 0; transform: translateY(20px);'">
                    Music Management, Label and Music Production
                </h2>

                <!-- Description -->
                <div class="space-y-6 text-lg transition-all duration-1000 transform "
                    :style="showDesc ?
                        'opacity: 1; transform: translateX(0);' :
                        'opacity: 0; transform: translateX(50px);'">

                    <!-- Main Description -->

                    <div class="flex flex-col gap-4">
                        <!-- Music Genres -->
                        <p class="mt-8 text-lg leading-relaxed text-gray-300 transition-all duration-1000 transform"
                            :style="showDesc ?
                                'opacity: 1; transform: translateX(0);' :
                                'opacity: 0; transform: translateX(50px);'">
                            Snow 'n' Stuff is releasing Tech House, Deep House, House and Techno.</p>

                        <!-- Management Info -->
                        <div class="text-center">
                            <span class="text-red-500">Management for:</span>
                            <div class="flex flex-wrap justify-center gap-2 mt-2">
                                <span
                                    class="px-3 py-1 text-sm transition-colors duration-300 rounded-md bg-white/5 hover:bg-white/10">THK</span>
                                <span
                                    class="px-3 py-1 text-sm transition-colors duration-300 rounded-md bg-white/5 hover:bg-white/10">G&S</span>
                                <span
                                    class="px-3 py-1 text-sm transition-colors duration-300 rounded-md bg-white/5 hover:bg-white/10">Snow
                                    N Stuff</span>
                                <span
                                    class="px-3 py-1 text-sm transition-colors duration-300 rounded-md bg-white/5 hover:bg-white/10">Style
                                    Da Kid</span>
                            </div>
                        </div>

                        <!-- Curator Info -->
                        <div class="text-center text-gray-300">
                            <div class="inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-500"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 18V5l12-2v13" />
                                    <circle cx="6" cy="18" r="3" />
                                    <circle cx="18" cy="16" r="3" />
                                </svg>
                                <span class="font-medium">Tastemaker & Curator of several Spotify playlists</span>
                            </div>
                        </div>
                    </div>
                </div>
            
            <!-- Buttons -->
            <div class="flex flex-col items-center justify-center gap-4 my-12 sm:flex-row"
                :class="showButtons ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                <a href="#releases"
                    class="group relative px-6 py-2.5 w-full sm:w-auto overflow-hidden bg-gradient-to-r from-red-800 to-red-700 rounded-lg hover:from-red-700 hover:to-red-600 transition-all duration-300">
                    <span class="relative z-10 font-medium text-white">Our Music</span>
                </a>
                <a href="#artists"
                    class="px-6 py-2.5 w-full sm:w-auto text-white font-medium border-2 border-red-800/50 rounded-lg hover:bg-red-800/20 transition-all duration-300">
                    Our Artists
                </a>
                <a href="/blog"
                    class="px-6 py-2.5 w-full sm:w-auto text-white font-medium border-2 border-red-800/50 rounded-lg hover:bg-red-800/20 transition-all duration-300">
                    Blog/Latest News
                </a>
            </div>
        </div>
    </div>
    </div>
</section>
