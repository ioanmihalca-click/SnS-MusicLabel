<section class="relative min-h-screen pt-32 overflow-hidden">
    <div class="absolute inset-0 bg-fixed bg-center bg-cover"
        style="background-image: url('/assets/img/music-bg.jpg')">
        <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/70 to-black/90"></div>
    </div>

    <div class="container relative px-4 pt-24 mx-auto">
        <div class="max-w-4xl mx-auto text-center"
             x-data="{ 
                show: false,
                showTitle: false,
                showBrand: false,
                showSubtitle: false,
                showDescription: false,
                showButtons: false
             }"
             x-init="
                setTimeout(() => showTitle = true, 300);
                setTimeout(() => showBrand = true, 800);
                setTimeout(() => showSubtitle = true, 1300);
                setTimeout(() => showDescription = true, 1800);
                setTimeout(() => showButtons = true, 2300)
             ">
            
            <!-- Main Title - Split into two parts -->
            <h1 class="flex flex-wrap items-center justify-center text-5xl font-bold md:text-7xl gap-x-4">
                <!-- "Welcome to" text -->
                <span
                    x-show="showTitle"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 transform -translate-x-8"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    class="inline-block">
                    Welcome to
                </span>
                
                <!-- "Snow n Stuff" text -->
                <span
                    x-show="showBrand"
                    x-transition:enter="transition ease-out duration-1000"
                    x-transition:enter-start="opacity-0 transform scale-50"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="inline-block text-transparent bg-clip-text bg-gradient-to-r from-red-800 to-red-500 animate-gradient">
                    Snow n Stuff
                </span>
            </h1>

            <!-- Subtitle -->
            <h2 class="mt-6 text-2xl font-light text-gray-300 md:text-3xl"
                x-show="showSubtitle"
                x-transition:enter="transition ease-out duration-1000"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0">
                Music Management, Label and Music Production
            </h2>

            <!-- Description -->
            <p class="mt-8 text-lg leading-relaxed text-gray-300"
               x-show="showDescription"
               x-transition:enter="transition ease-out duration-1000"
               x-transition:enter-start="opacity-0 transform translate-x-8"
               x-transition:enter-end="opacity-100 transform translate-x-0">
                Snow 'n' Stuff is releasing Tech House, Deep House, House and Techno.
                <br class="hidden md:block">
                Management for: THK, G&S, Snow N Stuff and Style Da Kid among others.
                <br class="hidden md:block">
                Tastemaker & Curator of several Spotify playlists.
            </p>

            <!-- Buttons -->
            <div class="flex flex-col items-center justify-center gap-4 mt-12 sm:flex-row"
                 x-show="showButtons"
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">
                <a href="#releases"
                    class="w-full px-4 py-2 text-center text-white transition-all duration-300 border-2 border-red-800 hover:bg-red-800 hover:scale-105 sm:w-auto">
                    Our Music
                </a>
                <a href="#artists"
                    class="w-full px-4 py-2 text-center text-white transition-all duration-300 border-2 border-red-800 hover:bg-red-800 hover:scale-105 sm:w-auto">
                    Our Artists
                </a>
                <a href="/blog"
                    class="w-full px-4 py-2 text-center text-white transition-all duration-300 border-2 border-red-800 hover:bg-red-800 hover:scale-105 sm:w-auto">
                    Blog/Latest News
                </a>
            </div>
        </div>
    </div>
</section>