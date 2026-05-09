@props([
    'title' => "Snow 'n' Stuff - Music Management, Label and Music Production",
    'description' => null,
    'keywords' => null,
    'ogImage' => null,
    'preloadImage' => '/assets/img/music-bg.jpg',
])

@php
    $defaultDescription = "Snow 'n' Stuff is an innovative music label specializing in Tech House, Deep House, House, and Techno. Discover exceptional artists and immersive live events curated by industry veterans.";
    $defaultKeywords = 'snow n stuff, tech house, deep house, house music, techno, electronic music, music label, music production, artist development, live events';
    $defaultOgImage = 'https://snow-n-stuff.com/assets/img/OG-SnownStuff.jpg';
@endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<x-partials.head
    :title="$title"
    :description="$description ?? $defaultDescription"
    :keywords="$keywords ?? $defaultKeywords"
    :og-image="$ogImage ?? $defaultOgImage"
    :preload-image="$preloadImage"
>
    @verbatim
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
        { "@type": "Person", "name": "Glenn Forrestgate" },
        { "@type": "Person", "name": "Style da Kid" }
      ],
      "genre": ["Tech House", "Deep House", "House", "Techno"]
    }
    </script>
    @endverbatim
</x-partials.head>

<body class="text-white bg-black">
    <x-partials.preloader />
    <x-back-to-top />

    <x-top-bar />

    {{ $slot }}

    <x-footer />

    @livewireScripts
</body>

</html>
