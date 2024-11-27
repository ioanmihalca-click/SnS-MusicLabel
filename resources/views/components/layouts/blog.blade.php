<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta_title ?? "Snow 'n' Stuff Blog" }}</title>
    
    <!-- SEO Meta Tags -->
    <link rel="canonical" href="{{ url()->current() }}" />
    <meta name="description" content="{{ $meta_description ?? "Snow 'n' Stuff blog - Music Management, Label and Music Production insights" }}">
    <meta name="keywords" content="{{ $meta_keywords ?? 'blog, snow n stuff, tech house, deep house, house music, techno, electronic music, music label, music production, artist development' }}">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="{{ $meta_title ?? "Snow 'n' Stuff Blog" }}" />
    <meta property="og:description" content="{{ $meta_description ?? "Snow 'n' Stuff blog - Music Management, Label and Music Production insights" }}" />
    <meta property="og:image" content="{{ $og_image ?? 'https://snow-n-stuff.com/assets/img/OG-SnownStuff.jpg' }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="article" />
    <meta property="og:locale" content="en_EU" />
    <meta property="og:site_name" content="Snow 'n' Stuff Blog" />

    <!-- Favicons -->
    <link href="/assets/img/favicon.ico" rel="icon">
    <link href="/assets/img/apple-touch-icon.ico" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    @vite('resources/css/app.css')
    @livewireStyles

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1PQQSTPYZC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-1PQQSTPYZC');
    </script>
</head>

<body class="flex flex-col min-h-screen text-white bg-black">
    <!-- Top Bar -->
    <div 
        x-data="{ isVisible: true }"
        @scroll.window="isVisible = window.pageYOffset < 100"
        x-show="isVisible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-4"
        class="fixed top-0 z-50 w-full py-2 bg-black/90 backdrop-blur-sm"
    >
        <div class="container px-4 mx-auto">
            <div class="flex items-center justify-center md:justify-between">
                <div class="flex space-x-6 text-sm">
                    <a href="mailto:info@1namm.com" class="flex items-center space-x-2 text-gray-300 transition-colors duration-300 hover:text-red-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>info@1namm.com</span>
                    </a>
                    <a href="mailto:glenn@1namm.com" class="flex items-center space-x-2 text-gray-300 transition-colors duration-300 hover:text-red-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>glenn@1namm.com</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header 
        x-data="{ isScrolled: false, mobileMenuOpen: false }"
        @scroll.window="isScrolled = window.pageYOffset > 100"
        :class="{'bg-black/95': isScrolled, 'bg-transparent': !isScrolled}"
        class="fixed z-40 w-full transition-colors duration-300"
        style="top: 2.5rem;"
    >
        <div class="container px-4 mx-auto">
            <nav class="flex items-center justify-between py-4">
                <!-- Logo -->
                <a href="/" class="text-2xl font-bold text-white transition-colors duration-300 hover:text-red-800">
                    Snow n Stuff
                </a>

                <!-- Desktop Navigation -->
                <div class="items-center hidden space-x-8 md:flex">
                    <a href="/#hero" class="text-white transition-colors duration-300 hover:text-red-800">Home</a>
                    <a href="/#about" class="text-white transition-colors duration-300 hover:text-red-800">About</a>
                    <a href="/#artists" class="text-white transition-colors duration-300 hover:text-red-800">Artists</a>
                    <a href="/#releases" class="text-white transition-colors duration-300 hover:text-red-800">Releases</a>
                    <a href="/#playlists" class="text-white transition-colors duration-300 hover:text-red-800">Playlists</a>
                    <a href="/blog" class="text-red-800 transition-colors duration-300 hover:text-red-700">Blog</a>
                    <a href="/#gallery" class="text-white transition-colors duration-300 hover:text-red-800">Gallery</a>
                    <a href="/#contact" class="text-white transition-colors duration-300 hover:text-red-800">Contact</a>
                </div>

                <!-- Mobile Menu Button -->
                <button 
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="p-2 text-white transition-colors duration-300 md:hidden hover:text-red-800"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Mobile Menu -->
                <div 
                    x-show="mobileMenuOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 right-0 top-full bg-black/95 backdrop-blur-sm md:hidden"
                >
                    <div class="px-4 py-6 space-y-4">
                        <a href="/#hero" class="block text-white transition-colors duration-300 hover:text-red-800">Home</a>
                        <a href="/#about" class="block text-white transition-colors duration-300 hover:text-red-800">About</a>
                        <a href="/#artists" class="block text-white transition-colors duration-300 hover:text-red-800">Artists</a>
                        <a href="/#releases" class="block text-white transition-colors duration-300 hover:text-red-800">Releases</a>
                        <a href="/#playlists" class="block text-white transition-colors duration-300 hover:text-red-800">Playlists</a>
                        <a href="/blog" class="block text-red-800 transition-colors duration-300 hover:text-red-700">Blog</a>
                        <a href="/#gallery" class="block text-white transition-colors duration-300 hover:text-red-800">Gallery</a>
                        <a href="/#contact" class="block text-white transition-colors duration-300 hover:text-red-800">Contact</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-32">
        <div class="container px-4 mx-auto">
            {{ $slot }}
        </div>
    </main>

    <!-- Back to Top Button -->
    <button
        x-data="{ show: false }"
        x-init="window.addEventListener('scroll', () => { show = window.pageYOffset > 500 })"
        x-show="show"
        x-transition.opacity.duration.300ms
        @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="fixed z-50 p-3 transition-colors duration-300 bg-transparent border-2 border-red-800 rounded-full bottom-6 right-6 hover:bg-red-800 group"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-800 transition-colors duration-300 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    <!-- Footer -->
    <footer class="mt-12 bg-black border-t border-gray-900">
        <div class="container px-4 py-8 mx-auto">
            <div class="text-center">
                <p class="text-gray-400">&copy; Copyright <strong>Snow n Stuff</strong>. All Rights Reserved</p>
                <p class="mt-2 text-gray-500">
                    Web application by <a href="https://clickstudios-digital.com" class="text-red-800 transition-colors duration-300 hover:text-red-700">Click Studios Digital</a>
                </p>
            </div>
        </div>
    </footer>

    @livewireScripts

</body>
</html>