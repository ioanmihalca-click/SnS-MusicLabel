@props([
    'seo' => new \App\Support\Seo\SeoData(path: request()->getPathInfo()),
    'fonts' => 'big-shoulders-display:700,800,900|schibsted-grotesk:400,400i,500,600,700|martian-mono:400,500',
])

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <x-seo :seo="$seo" />

    {{-- Favicons --}}
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="SnS" />
    <link rel="manifest" href="/assets/favicon/site.webmanifest" />

    {{-- Bunny Fonts (GDPR-safe Google Fonts proxy) --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin />
    <link
        rel="stylesheet"
        href="https://fonts.bunny.net/css?family={{ $fonts }}&display=swap"
    />

    {{ $slot }}

    {{-- Google Analytics: production only, until the consent banner lands --}}
    @production
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-1PQQSTPYZC"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', 'G-1PQQSTPYZC');
        </script>
    @endproduction

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
