{{--
    The redesigned site (stage 2 onwards): cold near-black ground, Big Shoulders
    Display headings, Schibsted Grotesk text and Martian Mono metadata. Used by
    the catalogue pages, /about and the 404 page; the homepage and the blog
    keep components.layouts.app / .blog until stage 3.
--}}
@props([
    'seo',
])

<!DOCTYPE html>
<html lang="en" class="theme-site bg-ink">

<x-partials.head
    :seo="$seo"
    fonts="big-shoulders-display:700,800,900|schibsted-grotesk:400,400i,500,600,700|martian-mono:400,500"
/>

<body class="flex min-h-screen flex-col bg-ink font-body text-base leading-[1.55] text-frost antialiased">
    <a
        href="#main"
        data-markdown-ignore
        class="sr-only z-[60] rounded-full bg-frost px-4 py-2 text-sm font-semibold text-ink focus:not-sr-only focus:fixed focus:left-4 focus:top-4"
    >Skip to content</a>

    <x-site.header />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-site.footer />

    @livewireScripts
</body>

</html>
