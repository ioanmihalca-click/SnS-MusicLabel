@props([
    'seo' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark theme-legacy">

<x-partials.head :seo="$seo" />

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
