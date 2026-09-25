@props([
    'seo' => null,
    'preloadImage' => '/assets/img/music-bg.jpg',
])

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<x-partials.head :seo="$seo" :preload-image="$preloadImage" />

<body class="text-white bg-black">
    <x-partials.preloader />
    <x-back-to-top />

    <x-top-bar />

    <main>
        {{ $slot }}
    </main>

    <x-footer />

    @livewireScripts
</body>

</html>
