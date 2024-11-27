<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snow 'n' Stuff - Music Management, Label and Music Production</Label></title>

    <link rel="canonical" href="{{ url()->current() }}" />

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

<body class="text-white bg-black">
    <!-- Preloader cu animație mai subtilă -->
    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 1000)" x-show="loading" x-transition.opacity.duration.500ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-black">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-gray-800 rounded-full border-t-red-800 animate-spin"></div>
            <div class="absolute border-4 border-gray-800 rounded-full inset-2 border-t-red-600 animate-spin-slow">
            </div>
        </div>
    </div>

    <!-- Back to Top cu animație smooth -->
    <button x-data="{ show: false }" x-init="window.addEventListener('scroll', () => { show = window.pageYOffset > 500 })" x-show="show" x-transition.opacity.duration.300ms
        @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="fixed z-50 p-3 transition-all duration-300 border-2 border-red-800 rounded-full bg-black/80 bottom-8 right-8 hover:bg-red-800 hover:scale-110 group">
        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6 text-red-800 transition-colors duration-300 group-hover:text-white" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <x-top-bar />

  <livewire:hero />

     

            <!-- Latest Articles -->
            {{-- <div class="max-w-3xl mx-auto mt-24">
            <div class="mb-12 text-center">
                <h2 class="text-sm tracking-wider text-gray-400 uppercase">Latest Updates</h2>
                <p class="mt-2 text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-800 to-red-500">
                    Recent News
                </p>
            </div>
            <div class="relative overflow-hidden rounded-lg shadow-2xl shadow-red-900/20">
                <livewire:latest-articles />
            </div>
        </div> --}}
        </div>
    </section>

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
</body>

</html>
