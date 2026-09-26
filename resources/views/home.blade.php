@php
    $sectionClass = 'site-wrap pt-[clamp(64px,9vw,120px)]';
    $moreLinkClass = 'inline-flex items-center gap-2 border-b border-rule2 pb-[3px] font-semibold transition-colors hover:border-frost';
    $monoClass = 'm-0 font-meta text-[11px] uppercase tracking-[.08em]';
    $gridClass = 'grid grid-cols-2 gap-x-3.5 gap-y-[26px] sm:grid-cols-3 sm:gap-x-[22px] sm:gap-y-9 lg:grid-cols-4';
    $contacts = [
        'Bookings, remixes, sync' => 'glenn@1namm.com',
        'Licensing' => 'info@1namm.com',
        'Demos' => 'demo@1namm.com',
    ];
    $followLinks = [
        ['label' => 'Spotify', 'handle' => 'Snow N Stuff', 'href' => 'https://open.spotify.com/artist/6wIX9hW2uQAVv190xXV9mA'],
        ['label' => 'Instagram', 'handle' => '@snow_n_stuff', 'href' => 'https://www.instagram.com/snow_n_stuff'],
        ['label' => 'Facebook', 'handle' => 'SnowNStuff', 'href' => 'https://www.facebook.com/SnowNStuff'],
        ['label' => 'X', 'handle' => '@G_n_S_', 'href' => 'https://x.com/G_n_S_'],
    ];
    $artistFilters = $artists->where('releases_count', '>', 0);
@endphp

<x-layouts.site :seo="$seo">
    <h1 class="sr-only">Snow 'n' Stuff</h1>

    <x-home.hero :release="$heroRelease" :next="$nextRelease" />

    @if ($latestReleases->isNotEmpty())
        <section id="releases" class="{{ $sectionClass }}">
            <x-section-heading title="Releases" :count="$releases->count()" subtitle="Every release on the label, newest first.">
                @if ($artistFilters->isNotEmpty())
                    <nav aria-label="Releases by artist" data-markdown-ignore class="flex flex-wrap gap-2">
                        @foreach ($artistFilters as $artist)
                            <a
                                href="{{ route('releases.index', ['artist' => $artist->slug]) }}"
                                wire:navigate
                                class="inline-flex items-center rounded-full border border-rule2 px-3.5 py-[9px] text-[13px] font-medium leading-none text-mist transition-colors hover:border-mist hover:text-frost"
                            >{{ $artist->name }}<span class="ml-1.5 font-meta text-[10px] opacity-70">{{ $artist->releases_count }}</span></a>
                        @endforeach
                    </nav>
                @endif
            </x-section-heading>

            <div class="{{ $gridClass }}">
                @foreach ($latestReleases as $release)
                    <x-release-card :release="$release" />
                @endforeach
            </div>

            <div class="mt-10 flex justify-center">
                <x-site.button :href="route('releases.index')">View all {{ $releases->count() }} releases</x-site.button>
            </div>
        </section>
    @endif

    <x-home.support :releases="$releases" />

    @if ($artists->isNotEmpty())
        <section class="{{ $sectionClass }}">
            <x-section-heading title="Artists" :count="$artists->count()" subtitle="Roster and management.">
                <a href="{{ route('artists.index') }}" wire:navigate class="{{ $moreLinkClass }}">All artists <span aria-hidden="true">→</span></a>
            </x-section-heading>

            <div class="grid grid-cols-2 gap-3.5 md:grid-cols-4 md:gap-5">
                @foreach ($artists as $artist)
                    <x-artist-card :artist="$artist" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($playlists->isNotEmpty())
        <section class="{{ $sectionClass }}">
            <x-section-heading title="Playlists" :count="$playlists->count()">
                <a href="{{ route('playlists.index') }}" wire:navigate class="{{ $moreLinkClass }}">All playlists <span aria-hidden="true">→</span></a>
            </x-section-heading>

            <p class="-mt-4 mb-8 inline-flex items-center gap-2 text-sm text-mist">
                <x-icons.spotify class="h-[18px] w-[18px] text-[#1ED760]" />
                Tastemaker &amp; curator on Spotify
            </p>

            <div class="relative grid snap-x snap-mandatory auto-cols-[minmax(200px,1fr)] grid-flow-col gap-5 overflow-x-auto pb-2.5 [scrollbar-color:theme(colors.rule2)_transparent] [scrollbar-width:thin]">
                @foreach ($playlists as $playlist)
                    <x-playlist-card :playlist="$playlist" heading-level="h3" class="snap-start" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="{{ $sectionClass }}">
        <x-section-heading title="About">
            <p class="{{ $monoClass }} text-mist">Est. 2020 · Stockholm &amp; Romania</p>
        </x-section-heading>

        <div class="grid items-start gap-[clamp(28px,5vw,72px)] md:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
            <div class="flex max-w-[60ch] flex-col gap-[18px]">
                <p class="m-0 text-[clamp(1.3rem,2.2vw,1.65rem)] font-medium leading-[1.3]">Music management, label and production for Tech House, Deep House, House and Techno.</p>

                <p class="m-0 text-mist">More than two and a half decades of A&amp;R, production, mixing, mastering and sound design. Grammy nominations, licensing worldwide, sync placements across international networks and millions of radio plays.</p>

                <p class="{{ $monoClass }} text-frost">Management for THK · G&amp;S · Snow 'n' Stuff · Style da Kid</p>

                <dl class="m-0 grid gap-x-6 gap-y-3 border-t border-rule pt-[18px] sm:grid-cols-3">
                    @foreach ($contacts as $purpose => $email)
                        <div>
                            <dt class="text-[13px] text-dim">{{ $purpose }}</dt>
                            <dd class="m-0"><a href="mailto:{{ $email }}" class="text-[15px] text-frost underline-offset-4 hover:underline">{{ $email }}</a></dd>
                        </div>
                    @endforeach
                </dl>

                <div>
                    <a href="{{ route('about') }}" wire:navigate class="{{ $moreLinkClass }}">Read the full story <span aria-hidden="true">→</span></a>
                </div>
            </div>

            @if ($photos->isNotEmpty())
                <div data-markdown-ignore class="grid grid-cols-3 gap-2">
                    @foreach ($photos as $photo)
                        <a href="{{ route('about') }}#gallery" wire:navigate class="group block aspect-square overflow-hidden bg-slab">
                            <img
                                src="{{ $photo->imageUrl() }}"
                                alt="{{ $photo->title }}"
                                width="400"
                                height="400"
                                loading="lazy"
                                decoding="async"
                                class="h-full w-full object-cover brightness-[.85] contrast-[1.1] grayscale transition duration-500 group-hover:brightness-100 group-hover:contrast-100 group-hover:grayscale-0"
                            >
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($news->isNotEmpty())
        <section class="{{ $sectionClass }}">
            <x-section-heading title="News" subtitle="Releases, charts and label updates.">
                <a href="{{ route('blog.index') }}" wire:navigate class="{{ $moreLinkClass }}">All news <span aria-hidden="true">→</span></a>
            </x-section-heading>

            <div class="grid gap-x-[22px] gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($news as $item)
                    <x-news-card :item="$item" />
                @endforeach
            </div>
        </section>
    @endif

    <section aria-labelledby="follow-title" class="mt-[clamp(64px,9vw,120px)] border-y border-rule bg-slab">
        <div class="site-wrap grid gap-x-[clamp(28px,4vw,64px)] gap-y-8 py-[clamp(44px,6vw,80px)] md:grid-cols-2">
            <div class="flex flex-col gap-5">
                <p class="{{ $monoClass }} text-mist">Follow</p>
                <h2 id="follow-title" class="m-0 font-display text-[clamp(2.4rem,4.6vw,3.8rem)] font-extrabold uppercase leading-[.86]">Stay in the loop</h2>
                <p class="m-0 max-w-[52ch] text-mist">New releases land on Spotify first. Follow the label and the artists to get them on release day.</p>
            </div>

            <ul class="m-0 flex list-none flex-col border-t border-rule p-0 md:self-end">
                @foreach ($followLinks as $link)
                    <li>
                        <a
                            href="{{ $link['href'] }}"
                            target="_blank"
                            rel="noopener"
                            class="flex items-baseline justify-between gap-3 border-b border-rule py-4 font-display text-[clamp(1.6rem,2.6vw,2.2rem)] font-extrabold uppercase leading-none transition-colors hover:text-white [&:hover_small]:text-mist"
                        >{{ $link['label'] }} <small class="font-meta text-[10px] font-normal tracking-[.08em] text-dim">{{ $link['handle'] }}</small></a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
</x-layouts.site>
