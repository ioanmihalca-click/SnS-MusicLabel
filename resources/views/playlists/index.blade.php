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
                    @php
                        $coverUrl = $playlist->coverUrl();
                    @endphp

                    <article class="flex min-w-0 flex-col gap-3">
                        <div data-markdown-ignore class="aspect-square overflow-hidden bg-slab">
                            @if ($coverUrl)
                                <img
                                    src="{{ $coverUrl }}"
                                    alt="Cover of the {{ $playlist->displayTitle() }} playlist"
                                    width="300"
                                    height="300"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <span class="grid h-full w-full place-items-center">
                                    <x-icons.spotify class="h-1/4 w-1/4 text-rule2" />
                                </span>
                            @endif
                        </div>

                        <h2 class="m-0 text-base font-semibold leading-tight">{{ $playlist->displayTitle() }}</h2>

                        @if (filled($playlist->description))
                            <p class="m-0 font-meta text-[10px] uppercase leading-[1.7] tracking-[.08em] text-dim">{{ $playlist->description }}</p>
                        @endif

                        @if (filled($playlist->spotify_url))
                            <div>
                                <a
                                    href="{{ $playlist->spotify_url }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-[7px] rounded-full border border-rule2 bg-ink/35 px-[13px] py-[9px] text-[13px] font-medium leading-none transition-colors hover:border-frost"
                                >
                                    <x-icons.spotify class="h-[15px] w-[15px]" />
                                    Follow<span class="sr-only"> {{ $playlist->displayTitle() }} on Spotify</span>
                                </a>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <p class="text-mist">New playlists are on the way.</p>
        @endif
    </section>
</x-layouts.site>
