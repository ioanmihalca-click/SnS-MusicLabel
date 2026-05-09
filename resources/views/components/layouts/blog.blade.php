@props([
    'meta_title' => "Snow 'n' Stuff Blog",
    'meta_description' => "Snow 'n' Stuff blog - Music Management, Label and Music Production insights",
    'meta_keywords' => 'blog, snow n stuff, tech house, deep house, house music, techno, electronic music, music label, music production, artist development',
    'og_image' => 'https://snow-n-stuff.com/assets/img/OG-SnownStuff.jpg',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">

<x-partials.head
    :title="$meta_title"
    :description="$meta_description"
    :keywords="$meta_keywords"
    :og-image="$og_image"
    og-type="article"
    og-site-name="Snow 'n' Stuff Blog"
/>

<body class="flex flex-col min-h-screen text-white bg-black">
    <x-partials.preloader />
    <x-back-to-top />

    <x-top-bar />

    <main class="flex-grow pt-32">
        <div class="container px-4 mx-auto">
            {{ $slot }}
        </div>
    </main>

    <x-footer />

    @livewireScripts
</body>

</html>
