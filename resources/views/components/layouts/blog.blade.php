<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta_title ?? "Snow 'n' Stuff Blog" }}</title>

    <!-- SEO Meta Tags -->
    <link rel="canonical" href="{{ url()->current() }}" />
    <meta name="description"
        content="{{ $meta_description ?? "Snow 'n' Stuff blog - Music Management, Label and Music Production insights" }}">
    <meta name="keywords"
        content="{{ $meta_keywords ?? 'blog, snow n stuff, tech house, deep house, house music, techno, electronic music, music label, music production, artist development' }}">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="{{ $meta_title ?? "Snow 'n' Stuff Blog" }}" />
    <meta property="og:description"
        content="{{ $meta_description ?? "Snow 'n' Stuff blog - Music Management, Label and Music Production insights" }}" />
    <meta property="og:image" content="{{ $og_image ?? 'https://snow-n-stuff.com/assets/img/OG-SnownStuff.jpg' }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="article" />
    <meta property="og:locale" content="en_EU" />
    <meta property="og:site_name" content="Snow 'n' Stuff Blog" />

        <!-- Favicons -->
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="SnS" />
    <link rel="manifest" href="/assets/favicon/site.webmanifest" />


    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1PQQSTPYZC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-1PQQSTPYZC');
    </script>

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    @livewireStyles
</head>

<body class="flex flex-col min-h-screen text-white bg-black">
    <!-- Top Bar -->
    <x-top-bar />

    <!-- Main Content -->
    <main class="flex-grow pt-32">
        <div class="container px-4 mx-auto">
            {{ $slot }}
        </div>
    </main>

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

    <!-- Footer -->
    <footer class="mt-12 bg-black border-t border-gray-900">
        <div class="container px-4 py-8 mx-auto">
            <div class="text-center">
                <p class="text-gray-400">&copy; Copyright <strong>Snow n Stuff</strong>. All Rights Reserved</p>
                <p class="mt-2 text-gray-500">
                    Web application by <a href="https://clickstudios-digital.com"
                        class="text-red-800 transition-colors duration-300 hover:text-red-700">Click Studios
                        Digital</a>
                </p>
            </div>
        </div>
    </footer>

    @livewireScripts

</body>

</html>
