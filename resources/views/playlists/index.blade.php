<x-layouts.site :seo="$seo">
    <section class="site-wrap pt-[clamp(32px,5vw,64px)]">
        <x-breadcrumbs :trail="$trail" class="mb-6" />

        <x-section-heading as="h1" title="Playlists" :count="$playlists->count()">
            <p class="m-0 inline-flex items-center gap-2 text-sm text-mist">
                <x-icons.spotify class="h-[18px] w-[18px] text-[#1ED760]" />
                Tastemaker &amp; curator on Spotify
            </p>
        </x-section-heading>

        @if ($playlists->isNotEmpty())
            <div class="grid grid-cols-2 gap-x-3.5 gap-y-8 sm:grid-cols-3 sm:gap-x-5 lg:grid-cols-5">
                @foreach ($playlists as $playlist)
                    <x-playlist-card :playlist="$playlist" />
                @endforeach
            </div>
        @else
            <p class="text-mist">New playlists are on the way.</p>
        @endif
    </section>
</x-layouts.site>
