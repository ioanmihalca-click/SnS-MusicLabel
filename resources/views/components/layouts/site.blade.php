{{--
    The site's layout: cold near-black ground, Big Shoulders Display headings,
    Schibsted Grotesk text and Martian Mono metadata. Used by every public page.
--}}
@props([
    'seo',
])

<!DOCTYPE html>
<html lang="en" class="theme-site bg-ink">

<x-partials.head :seo="$seo" />

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
