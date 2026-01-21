<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snow 'n' Stuff - Music Management, Label and Music Production</title>

    <link rel="canonical" href="{{ url()->current() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <meta name="description"
        content="Snow 'n' Stuff is an innovative music label specializing in Tech House, Deep House, House, and Techno. Discover exceptional artists and immersive live events curated by industry veterans.">

    <meta name="keywords"
        content="content=snow n stuff, tech house, deep house, house music, techno, electronic music, music label, music production, artist development, live events">

    <!-- Open Graph Tags for Social Media Sharing -->
    <meta property="og:title" content="Snow 'n' Stuff - Music Management, Label and Music Production" />
    <meta property="og:description"
        content="Snow 'n' Stuff is an innovative music label specializing in Tech House, Deep House, House, and Techno. Discover exceptional artists and immersive live events curated by industry veterans." />
    <meta property="og:image" content="https://snow-n-stuff.com/assets/img/OG-SnownStuff.jpg" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:image:alt" content="Snow 'n' Stuff Website" />
    <meta property="og:url" content="https://www.snow-n-stuff.com" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="en_EU" />
    <meta property="og:site_name" content="Snow 'n' Stuff" />


    <!-- Favicons -->
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="SnS" />
    <link rel="manifest" href="/assets/favicon/site.webmanifest" />


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0.28/dist/fancybox/fancybox.css" />

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Snow 'n' Stuff",
      "alternateName": "Snow N Stuff",
      "url": "https://www.snow-n-stuff.com",
      "logo": "https://www.snow-n-stuff.com/assets/img/Snownstuff%20Logo.png",
      "sameAs": [
        "https://www.facebook.com/SnowNStuff",
        "https://www.instagram.com/snow_n_stuff",
        "https://x.com/G_n_S_"
      ],
      "description": "Snow 'n' Stuff is an innovative music label specializing in Tech House, Deep House, House, and Techno. Discover exceptional artists and immersive live events curated by industry veterans.",
      "foundingDate": "2020",
      "founders": [
        {
          "@type": "Person",
          "name": "Glenn Forrestgate"
        },
        {
          "@type": "Person",
          "name": "Style da Kid"
        }
      ],
      "genre": [
        "Tech House",
        "Deep House",
        "House",
        "Techno"
      ]
    }
    </script>

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

<body class="text-white bg-black font-sans antialiased selection:bg-red-500 selection:text-white">

    <!-- Global Noise Overlay -->
    <div class="fixed inset-0 z-0 pointer-events-none opacity-[0.03] mix-blend-overlay" 
         style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');">
    </div>

    <!-- Background Gradients -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-red-900/15 rounded-full blur-[150px] -translate-x-1/2 -translate-y-1/2 animate-pulse-soft"></div>
        <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-red-900/8 rounded-full blur-[150px] translate-x-1/2 translate-y-1/2 animate-pulse-soft" style="animation-delay: 2s;"></div>
    </div>

    <!-- Preloader -->
    <div 
        x-data="{ loading: true }" 
        x-init="setTimeout(() => loading = false, 800)" 
        x-show="loading" 
        x-transition.opacity.duration.500ms
        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm"
        style="background: rgba(0, 0, 0, 0.9);"
    >
        <div class="grid w-24 h-24 place-items-center">
            <!-- Outer ring -->
            <div class="absolute w-24 h-24 rounded-full animate-spin"
                 style="border: 3px solid rgba(75, 75, 75, 0.3); border-top-color: #991b1b;"></div>
            
            <!-- Middle ring -->
            <div class="absolute w-16 h-16 rounded-full animate-[spin_1.5s_linear_infinite]"
                 style="border: 3px solid rgba(75, 75, 75, 0.2); border-top-color: #dc2626;"></div>
            
            <!-- Inner ring -->
            <div class="absolute w-8 h-8 rounded-full animate-[spin_2s_linear_infinite]"
                 style="border: 3px solid rgba(75, 75, 75, 0.15); border-top-color: #ef4444;"></div>
        </div>
    </div>

    <!-- Back to Top -->
    <button x-cloak x-data="{ show: false }" x-init="window.addEventListener('scroll', () => { show = window.pageYOffset > 500 })" x-show="show"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
        @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="fixed z-50 bottom-6 right-6 p-3 rounded-xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1 group"
        style="
            background: linear-gradient(145deg, rgba(30, 30, 30, 0.9), rgba(20, 20, 20, 0.95));
            border: 1px solid rgba(75, 75, 75, 0.15);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.4);
        ">
        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5 transition-colors duration-300 text-gray-400 group-hover:text-red-500" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <x-top-bar />

    <livewire:hero />

    <!-- Content Sections -->
    <x-about />
    <livewire:artists />
    <livewire:releases />
    <livewire:playlist-slider />
    <livewire:photo-gallery />
    <x-contact />

    <!-- Footer -->
    <x-footer />

    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0.28/dist/fancybox/fancybox.umd.js"></script>
    <script>
        Fancybox.bind('[data-fancybox]', {
            // Custom options
        });
    </script>
</body>

</html>
