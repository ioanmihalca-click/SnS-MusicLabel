@php
    $socialLinks = \Illuminate\Support\Arr::except($artist->profileUrls(), 'Spotify');
@endphp

<x-layouts.site :seo="$seo">
    <section class="site-wrap grid items-end gap-[clamp(28px,5vw,72px)] border-b border-rule pb-[clamp(32px,5vw,56px)] pt-[clamp(32px,5vw,64px)] md:grid-cols-[minmax(0,.9fr)_minmax(0,1.1fr)]">
        <div class="relative aspect-[4/5] w-full max-w-[520px] overflow-hidden bg-slab">
            @if ($photoUrl)
                <img
                    src="{{ $photoUrl }}"
                    alt="{{ $artist->name }}"
                    fetchpriority="high"
                    class="h-full w-full object-cover brightness-[.85] contrast-[1.15] grayscale"
                >
            @else
                <div class="grid h-full w-full place-items-center">
                    <x-icons.logomark class="h-1/4 w-1/4 text-rule2" />
                </div>
            @endif
        </div>

        <div class="relative flex min-w-0 flex-col gap-[18px]">
            <x-breadcrumbs :trail="$trail" />

            @if (filled($artist->role))
                <p class="m-0 font-meta text-[11px] uppercase tracking-[.08em] text-mist">{{ $artist->role }}</p>
            @endif

            <h1 class="m-0 font-display text-[clamp(4rem,11vw,10rem)] font-black uppercase leading-[.8] [overflow-wrap:anywhere]">{{ $artist->name }}</h1>

            @if (filled($artist->origin))
                <p class="m-0 text-lg text-frost">{{ $artist->origin }}</p>
            @endif

            @if (filled($artist->spotify_url) || filled($artist->pressKitUrl()))
                <div class="flex flex-wrap items-center gap-3">
                    @if (filled($artist->spotify_url))
                        <x-site.button :href="$artist->spotify_url" primary external>
                            <x-icons.spotify />
                            Follow on Spotify
                        </x-site.button>
                    @endif

                    @if (filled($artist->pressKitUrl()))
                        <x-site.button :href="$artist->pressKitUrl()" external>
                            Press kit (PDF)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 4v12m0 0l-5-5m5 5l5-5M5 20h14" /></svg>
                        </x-site.button>
                    @endif
                </div>
            @endif

            @if ($socialLinks !== [])
                <ul class="m-0 flex list-none flex-wrap items-center gap-2 p-0" aria-label="{{ $artist->name }} elsewhere">
                    @foreach ($socialLinks as $platform => $url)
                        <li>
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-full border border-rule2 px-[13px] py-[9px] text-[13px] font-medium leading-none transition-colors hover:border-frost">{{ $platform }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    <section class="site-wrap grid gap-[clamp(32px,5vw,80px)] pt-[clamp(40px,6vw,72px)] md:grid-cols-[minmax(0,1.2fr)_minmax(0,.8fr)]">
        <div>
            <h2 class="sr-only">Biography</h2>
            <div class="max-w-[64ch] space-y-4 text-[17px] leading-relaxed [&_a]:underline [&_a]:underline-offset-4 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:m-0 [&_ul]:list-disc [&_ul]:pl-5">
                {!! $artist->description !!}
            </div>
        </div>

        @if (filled($artist->highlights))
            <aside>
                <h2 class="m-0 mb-3.5 font-meta text-[11px] font-normal uppercase tracking-[.08em] text-mist">Highlights</h2>
                <ul class="m-0 list-none border-t border-rule p-0">
                    @foreach ($artist->highlights as $highlight)
                        <li class="border-b border-rule py-3.5 font-medium">{{ $highlight }}</li>
                    @endforeach
                </ul>
            </aside>
        @endif
    </section>

    @if ($artist->releases->isNotEmpty())
        <section class="site-wrap pt-[clamp(64px,9vw,120px)]">
            <x-section-heading title="Discography" :count="$artist->releases->count()" />

            <div class="grid grid-cols-2 gap-x-3.5 gap-y-[26px] sm:grid-cols-3 sm:gap-x-[22px] sm:gap-y-9 lg:grid-cols-4">
                @foreach ($artist->releases as $release)
                    <x-release-card :release="$release" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($otherArtists->isNotEmpty())
        <section class="site-wrap pt-[clamp(64px,9vw,120px)]">
            <x-section-heading title="Also on the label" />

            <div class="grid grid-cols-2 gap-3.5 md:grid-cols-4 md:gap-5">
                @foreach ($otherArtists as $otherArtist)
                    <x-artist-card :artist="$otherArtist" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
