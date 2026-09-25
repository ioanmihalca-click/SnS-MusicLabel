{{--
    Not found, in the redesigned layout. Kept out of the index: noindex, and
    no canonical nor Markdown alternate (SeoData skips both when noindex).
--}}
@php
    $seo = new \App\Support\Seo\SeoData(
        title: 'Page not found - '.\App\Support\Seo\SeoData::SITE_NAME,
        description: 'This page does not exist or has moved. Browse the releases, artists and playlists of '.\App\Support\Seo\SeoData::SITE_NAME.'.',
        path: request()->getPathInfo(),
        noindex: true,
    );
@endphp

<x-layouts.site :seo="$seo">
    <section class="site-wrap pb-6 pt-[clamp(48px,8vw,112px)]">
        <p class="m-0 font-meta text-[11px] uppercase tracking-[.08em] text-mist">Error 404</p>

        <h1 class="mb-0 mt-5 font-display text-[clamp(3.6rem,11vw,9rem)] font-black uppercase leading-[.82]">Page not found</h1>

        <p class="mt-6 max-w-[52ch] text-lg text-mist">This page does not exist or has moved. The whole catalogue is still here.</p>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-site.button :href="route('releases.index')" primary>Browse releases</x-site.button>
            <x-site.button :href="route('artists.index')">Artists</x-site.button>
            <x-site.button :href="route('home')">Home</x-site.button>
        </div>
    </section>
</x-layouts.site>
