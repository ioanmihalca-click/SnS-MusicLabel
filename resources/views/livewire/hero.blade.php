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
            <div class="border-b rounded-lg shadow-lg bg-black/20 border-red-800/20 shadow-red-900/10">
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
<div class="mt-8">
    <div class="flex items-center justify-center px-4 py-3 mx-auto space-x-3 text-gray-300 transition-all duration-300 rounded-lg bg-white/5 hover:bg-white/10 max-w-fit">
        <!-- Spotify Icon -->
         <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                                    </svg>
        
        <!-- Text -->
        <span class="text-sm font-medium md:text-base">
            Tastemaker & Curator of 
            <span class="block md:inline">several Spotify playlists</span>
        </span>
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
