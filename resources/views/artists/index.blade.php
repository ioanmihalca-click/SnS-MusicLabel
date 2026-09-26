<x-layouts.site :seo="$seo">
    <section class="site-wrap pt-[clamp(32px,5vw,64px)]">
        <x-breadcrumbs :trail="$trail" class="mb-6" />

        <x-section-heading as="h1" title="Artists" :count="$artists->count()" subtitle="Roster and management." />

        @if ($artists->isNotEmpty())
            <div class="grid grid-cols-2 gap-3.5 md:grid-cols-4 md:gap-5">
                @foreach ($artists as $artist)
                    <x-artist-card :artist="$artist" />
                @endforeach
            </div>
        @else
            <p class="text-mist">The roster will be announced soon.</p>
        @endif
    </section>
</x-layouts.site>
