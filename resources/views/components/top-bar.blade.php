<div class="relative">
    <!-- Top Bar - se ascunde la scroll -->
    <div x-data="{ isVisible: true }" 
         x-show="isVisible" 
         @scroll.window="isVisible = window.pageYOffset < 100"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-4"
         class="fixed top-0 z-50 w-full py-2 bg-black/90 backdrop-blur-sm">
        <div class="container px-4 mx-auto">
            <div class="flex items-center justify-center md:justify-between">
                <div class="flex space-x-6 text-sm">
                    <a href="mailto:info@1namm.com" class="flex items-center space-x-2 font-semibold text-gray-300 transition-colors duration-300 hover:text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>info@1namm.com</span>
                    </a>
                    <a href="mailto:glenn@1namm.com" class="flex items-center space-x-2 font-semibold text-gray-300 transition-colors duration-300 hover:text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>glenn@1namm.com</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation - devine sticky la scroll -->
    <header x-data="{ isScrolled: false, mobileMenuOpen: false }" 
            @scroll.window="isScrolled = window.pageYOffset > 100"
            :class="{ 
                'bg-black/95 shadow-lg': isScrolled, 
                'bg-transparent': !isScrolled,
                'top-0': isScrolled,
                'top-10': !isScrolled
            }"
            class="fixed z-40 w-full transition-all duration-300">
        <div class="container px-4 mx-auto">
            <nav class="flex items-center justify-between py-4">
                <!-- Logo -->
                <a href="/" class="text-2xl font-bold tracking-tight text-white transition-colors duration-300 hover:text-red-800">
                    Snow n Stuff
                </a>

                <!-- Desktop Navigation -->
                <div class="items-center hidden space-x-8 md:flex">
                    <a href="/" class="font-semibold text-white transition-colors duration-300 hover:text-red-700">Home</a>
                    <a href="#about" class="font-semibold text-white transition-colors duration-300 hover:text-red-700">About</a>
                    <a href="#artists" class="font-semibold text-white transition-colors duration-300 hover:text-red-700">Artists</a>
                    <a href="#releases" class="font-semibold text-white transition-colors duration-300 hover:text-red-700">Releases</a>
                    <a href="#playlists" class="font-semibold text-white transition-colors duration-300 hover:text-red-700">Playlists</a>
                    <a href="/blog" class="font-semibold text-white transition-colors duration-300 hover:text-red-700">Blog</a>
                    <a href="#gallery" class="font-semibold text-white transition-colors duration-300 hover:text-red-700">Gallery</a>
                    <a href="#contact" class="font-semibold text-white transition-colors duration-300 hover:text-red-700">Contact</a>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="p-2 text-white transition-colors duration-300 md:hidden hover:text-red-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </nav>

            <!-- Mobile Menu Panel -->
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4"
                 class="absolute inset-x-0 w-full p-4 mt-2 space-y-4 text-lg shadow-lg bg-black/95 backdrop-blur-sm md:hidden rounded-b-xl"
                 @click.away="mobileMenuOpen = false">
                <a href="/" @click="mobileMenuOpen = false"
                   class="block p-3 text-center text-white transition-colors duration-300 rounded-lg hover:bg-red-800/20 hover:text-red-800">Home</a>
                <a href="#about" @click="mobileMenuOpen = false"
                   class="block p-3 text-center text-white transition-colors duration-300 rounded-lg hover:bg-red-800/20 hover:text-red-800">About</a>
                <a href="#artists" @click="mobileMenuOpen = false"
                   class="block p-3 text-center text-white transition-colors duration-300 rounded-lg hover:bg-red-800/20 hover:text-red-800">Artists</a>
                <a href="#releases" @click="mobileMenuOpen = false"
                   class="block p-3 text-center text-white transition-colors duration-300 rounded-lg hover:bg-red-800/20 hover:text-red-800">Releases</a>
                <a href="#playlists" @click="mobileMenuOpen = false"
                   class="block p-3 text-center text-white transition-colors duration-300 rounded-lg hover:bg-red-800/20 hover:text-red-800">Playlists</a>
                <a href="/blog" @click="mobileMenuOpen = false"
                   class="block p-3 text-center text-white transition-colors duration-300 rounded-lg hover:bg-red-800/20 hover:text-red-800">Blog</a>
                <a href="#gallery" @click="mobileMenuOpen = false"
                   class="block p-3 text-center text-white transition-colors duration-300 rounded-lg hover:bg-red-800/20 hover:text-red-800">Gallery</a>
                <a href="#contact" @click="mobileMenuOpen = false"
                   class="block p-3 text-center text-white transition-colors duration-300 rounded-lg hover:bg-red-800/20 hover:text-red-800">Contact</a>
            </div>
        </div>
    </header>
</div>