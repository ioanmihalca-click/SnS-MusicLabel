@props([
    'title' => "Snow 'n' Stuff - Music Management, Label and Music Production",
    'description' => "Snow 'n' Stuff is an innovative music label specializing in Tech House, Deep House, House, and Techno. Discover exceptional artists and immersive live events curated by industry veterans.",
    'keywords' => 'snow n stuff, tech house, deep house, house music, techno, electronic music, music label, music production, artist development, live events',
    'ogTitle' => null,
    'ogDescription' => null,
    'ogImage' => 'https://snow-n-stuff.com/assets/img/OG-SnownStuff.jpg',
    'ogType' => 'website',
    'ogSiteName' => "Snow 'n' Stuff",
    'preloadImage' => null,
])

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    <link rel="canonical" href="{{ url()->current() }}" />

    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $keywords }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $ogTitle ?? $title }}" />
    <meta property="og:description" content="{{ $ogDescription ?? $description }}" />
    <meta property="og:image" content="{{ $ogImage }}" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:image:alt" content="Snow 'n' Stuff Website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="{{ $ogType }}" />
    <meta property="og:locale" content="en_EU" />
    <meta property="og:site_name" content="{{ $ogSiteName }}" />

    {{-- Favicons --}}
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="SnS" />
    <link rel="manifest" href="/assets/favicon/site.webmanifest" />

    @if ($preloadImage)
        <link rel="preload" as="image" href="{{ $preloadImage }}" fetchpriority="high" />
    @endif

    {{ $slot }}

    {{-- Google Analytics --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1PQQSTPYZC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-1PQQSTPYZC');
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
