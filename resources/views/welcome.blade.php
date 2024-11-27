<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snow 'n' Stuff - Music Management, Label and Music Production</title>

    <!-- Fancybox -->
<link 
  rel="stylesheet" 
  href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0.28/dist/fancybox/fancybox.css"
/>

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    @livewireStyles
</head>

<body class="text-white bg-black">
    <!-- Preloader -->
    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 1000)" x-show="loading" x-transition.opacity.duration.500ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900">
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 border-4 border-gray-900 rounded-full border-t-red-800 animate-spin"></div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button x-data="{ show: false }" x-init="window.addEventListener('scroll', () => { show = window.pageYOffset > 500 })" x-show="show" x-transition.opacity.duration.300ms
        @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="fixed z-50 p-3 transition-colors duration-300 bg-transparent border-2 border-red-800 rounded-full bottom-6 right-6 hover:bg-red-800 group">
        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6 text-red-800 transition-colors duration-300 group-hover:text-white" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <!-- Navigation Structure -->
    <div class="relative">
        <!-- Top Bar -->
        <div x-data="{ isVisible: true }" x-show="isVisible" @scroll.window="isVisible = window.pageYOffset < 100"
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
                        <a href="mailto:info@1namm.com"
                            class="flex items-center space-x-2 text-gray-300 transition-colors duration-300 hover:text-red-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>info@1namm.com</span>
                        </a>
                        <a href="mailto:glenn@1namm.com"
                            class="flex items-center space-x-2 text-gray-300 transition-colors duration-300 hover:text-red-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>glenn@1namm.com</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <header x-data="{ isScrolled: false, mobileMenuOpen: false }" @scroll.window="isScrolled = window.pageYOffset > 100"
            :class="{ 'bg-black/95': isScrolled, 'bg-transparent': !isScrolled }"
            class="fixed z-40 w-full transition-colors duration-300" style="top: 2.5rem;">
            <div class="container px-4 mx-auto">
                <nav class="flex items-center justify-between py-4">
                    <!-- Logo -->
                    <a href="/"
                        class="text-2xl font-bold tracking-tight text-white transition-colors duration-300 hover:text-red-800">
                        Snow n Stuff
                    </a>

                    <!-- Desktop Navigation -->
                    <div class="items-center hidden space-x-8 md:flex">
                        <a href="/" class="text-white transition-colors duration-300 hover:text-red-800">Home</a>
                        <a href="#about" class="text-white transition-colors duration-300 hover:text-red-800">About</a>
                        <a href="#artists"
                            class="text-white transition-colors duration-300 hover:text-red-800">Artists</a>
                        <a href="#releases"
                            class="text-white transition-colors duration-300 hover:text-red-800">Releases</a>
                        <a href="#playlists"
                            class="text-white transition-colors duration-300 hover:text-red-800">Playlists</a>
                        <a href="/blog" class="text-white transition-colors duration-300 hover:text-red-800">Blog</a>
                        <a href="#gallery"
                            class="text-white transition-colors duration-300 hover:text-red-800">Gallery</a>
                        <a href="#contact"
                            class="text-white transition-colors duration-300 hover:text-red-800">Contact</a>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 text-white transition-colors duration-300 md:hidden hover:text-red-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Mobile Menu -->
                    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 right-0 top-full bg-black/95 backdrop-blur-sm md:hidden">
                        <div class="px-4 py-6 space-y-4">
                            <a href="/"
                                class="block text-white transition-colors duration-300 hover:text-red-800">Home</a>
                            <a href="#about"
                                class="block text-white transition-colors duration-300 hover:text-red-800">About</a>
                            <a href="#artists"
                                class="block text-white transition-colors duration-300 hover:text-red-800">Artists</a>
                            <a href="#releases"
                                class="block text-white transition-colors duration-300 hover:text-red-800">Releases</a>
                            <a href="#playlists"
                                class="block text-white transition-colors duration-300 hover:text-red-800">Playlists</a>
                            <a href="/blog"
                                class="block text-white transition-colors duration-300 hover:text-red-800">Blog</a>
                            <a href="#gallery"
                                class="block text-white transition-colors duration-300 hover:text-red-800">Gallery</a>
                            <a href="#contact"
                                class="block text-white transition-colors duration-300 hover:text-red-800">Contact</a>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
    </div>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="relative min-h-screen pt-32 overflow-hidden">
            <div class="absolute inset-0 bg-center bg-cover"
                style="background-image: url('/assets/img/music-bg.jpg')">
                <div class="absolute inset-0 bg-black/70"></div>
            </div>

            <div class="container relative px-4 pt-16 mx-auto">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <!-- Hero Content -->
                    <div class="space-y-8 text-center lg:text-left">
                        <h1 class="text-4xl font-bold md:text-6xl">
                            Welcome to <span class="text-red-800">Snow n Stuff</span>
                        </h1>
                        <h2 class="text-xl text-gray-300 md:text-2xl">
                            Music Management, Label and Music Production
                        </h2>
                        <p class="text-lg leading-relaxed text-gray-300">
                            Snow 'n' Stuff is releasing Tech House, Deep House, House and Techno.<br>
                            Management for: THK, G&S, Snow N Stuff and Style Da Kid among others.<br>
                            Tastemaker & Curator of several Spotify playlists.
                        </p>

                        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row lg:justify-start">
                            <a href="#releases"
                                class="w-full px-8 py-3 text-center text-white transition-colors duration-300 bg-red-800 rounded-full hover:bg-red-900 sm:w-auto">
                                Our Music
                            </a>
                            <a href="#artists"
                                class="w-full px-8 py-3 text-center text-white transition-colors duration-300 border-2 border-red-800 rounded-full hover:bg-red-800 sm:w-auto">
                                Our Artists
                            </a>
                            <a href="/blog"
                                class="w-full px-8 py-3 text-center text-white transition-colors duration-300 border-2 border-red-800 rounded-full hover:bg-red-800 sm:w-auto">
                                Blog/Latest News
                            </a>
                        </div>
                    </div>

                    <!-- Latest Articles -->
                    <div class="mt-12">
                        <livewire:latest-articles />
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Sections -->
        <x-about />
        <livewire:artists />
        <livewire:releases />
        <livewire:playlist-slider />
        <livewire:photo-gallery />
        <x-contact />
    </main>

    <!-- Footer -->
    <x-footer />

    <!-- Scripts -->
    @livewireScripts

<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0.28/dist/fancybox/fancybox.umd.js"></script>
</body>

</html>
