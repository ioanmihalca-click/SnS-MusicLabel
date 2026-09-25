{{--
    The homepage hero: the featured release (or else the newest), its blurred
    artwork as the background and a record sliding out of the sleeve, which
    spins while the artwork is hovered. Below, the newest other release.
    Without any release, only the label's line is shown.
    Expects the `artists` relation to be loaded, and `tracks` on the release.
--}}
@props([
    'release' => null,
    'next' => null,
])

@php
    $coverUrl = $release?->coverUrl();
    $titleLength = mb_strlen((string) $release?->title);
    $titleSize = match (true) {
        $titleLength <= 12 => 'text-[clamp(4.4rem,12.5vw,11rem)]',
        $titleLength <= 24 => 'text-[clamp(3.4rem,8.5vw,7.5rem)]',
        default => 'text-[clamp(2.6rem,5.6vw,5rem)]',
    };
    $eyebrowClass = 'm-0 flex items-center gap-2.5 font-meta text-[11px] uppercase tracking-[.08em] text-mist';
    $monoClass = 'font-meta text-[11px] font-normal uppercase tracking-[.08em] text-mist';
@endphp

<section aria-labelledby="hero-title" class="relative isolate overflow-hidden border-b border-rule">
    @if ($coverUrl)
        <div
            aria-hidden="true"
            data-markdown-ignore
            class="absolute -inset-[15%] -z-20 bg-cover bg-center opacity-60 blur-[80px] saturate-[1.35]"
            style="background-image: url('{{ $coverUrl }}')"
        ></div>
    @endif
    <div aria-hidden="true" data-markdown-ignore class="hero-shade absolute inset-0 -z-10"></div>

    <div @class([
        'site-wrap grid items-center gap-[clamp(32px,5vw,80px)] pb-[clamp(48px,7vw,96px)] pt-[clamp(40px,7vw,96px)]',
        'md:grid-cols-[minmax(0,1.1fr)_minmax(0,.9fr)]' => $release !== null,
    ])>
        <div class="relative flex min-w-0 flex-col gap-[22px]">
            @if ($release)
                @php
                    $releaseUrl = route('releases.show', $release->slug);
                    $eyebrow = array_filter([$release->is_featured ? 'Featured release' : 'Latest release', $release->released_at?->format('d.m.Y')]);
                    $meta = array_filter([
                        $release->format?->getLabel(),
                        $release->tracks->count() === 1 ? $release->tracks->first()->duration : null,
                        $release->genre?->getLabel(),
                    ]);
                @endphp

                <p class="{{ $eyebrowClass }}">
                    <span aria-hidden="true" data-markdown-ignore class="live-dot h-2 w-2 flex-none rounded-full bg-signal"></span>
                    {{ implode(' · ', $eyebrow) }}
                </p>

                <h2 id="hero-title" class="m-0 font-display {{ $titleSize }} font-black uppercase leading-[.8] tracking-[-.005em] [overflow-wrap:anywhere]">
                    <a href="{{ $releaseUrl }}" class="decoration-2 underline-offset-[.08em] hover:underline">{{ $release->title }}</a>
                </h2>

                @if (filled($release->credit) || $meta !== [])
                    <p class="m-0 flex flex-wrap items-baseline gap-x-3.5 gap-y-1.5 text-[clamp(1.1rem,2vw,1.35rem)] font-medium">
                        <x-release-credit :release="$release" as="span" />
                        @if ($meta !== [])
                            <span class="{{ $monoClass }}">{{ implode(' · ', $meta) }}</span>
                        @endif
                    </p>
                @endif

                <x-listen-links :release="$release" />

                @if ($next)
                    @php
                        $nextCoverUrl = $next->coverUrl();
                        $nextDate = $next->released_at?->isFuture()
                            ? 'Out '.$next->released_at->format('d.m.Y')
                            : $next->released_at?->format('d.m.Y');
                    @endphp

                    <div class="flex max-w-[520px] items-center gap-3 border-t border-frost/[.14] pt-[18px]">
                        @if ($nextCoverUrl)
                            <img src="{{ $nextCoverUrl }}" alt="" data-markdown-ignore width="44" height="44" loading="lazy" decoding="async" class="h-11 w-11 flex-none object-cover">
                        @endif
                        <p class="m-0 min-w-0">
                            <span class="font-meta text-[11px] uppercase tracking-[.08em] text-signal">New</span>
                            <a href="{{ route('releases.show', $next->slug) }}" class="font-semibold hover:underline">{{ $next->title }}</a>
                            <small class="block text-[13px] text-mist">{{ implode(' · ', array_filter([$next->credit, $nextDate])) }}</small>
                        </p>
                    </div>
                @endif
            @else
                <p class="{{ $eyebrowClass }}">
                    <span aria-hidden="true" data-markdown-ignore class="live-dot h-2 w-2 flex-none rounded-full bg-signal"></span>
                    Record label · Est. 2020
                </p>

                <h2 id="hero-title" class="m-0 max-w-[14ch] font-display text-[clamp(3.4rem,8.5vw,7.5rem)] font-black uppercase leading-[.82]">Music management, label &amp; production</h2>

                <p class="m-0 max-w-[52ch] text-lg text-mist">Tech House, Deep House, House and Techno, from Stockholm and Romania.</p>

                <div>
                    <x-site.button :href="route('about')" primary>About the label</x-site.button>
                </div>
            @endif
        </div>

        @if ($release)
            <div class="group relative aspect-square w-[min(74%,420px)] justify-self-start max-md:order-first md:w-[min(100%,470px)] md:justify-self-center">
                <div
                    aria-hidden="true"
                    data-markdown-ignore
                    class="absolute inset-[2.5%] z-[1] translate-x-[20%] transition-transform duration-[1.1s] ease-[cubic-bezier(.2,.7,.1,1)] group-hover:translate-x-[38%] motion-reduce:transition-none motion-reduce:group-hover:translate-x-[20%]"
                >
                    <div class="vinyl absolute inset-0 rounded-full">
                        <div class="vinyl-label" @if ($coverUrl) style="background-image: url('{{ $coverUrl }}')" @endif></div>
                    </div>
                </div>

                @if ($coverUrl)
                    <img
                        src="{{ $coverUrl }}"
                        alt="Cover of {{ $release->title }}{{ filled($release->credit) ? ' by '.$release->credit : '' }}"
                        width="640"
                        height="640"
                        fetchpriority="high"
                        class="relative z-[2] h-full w-full object-cover shadow-[0_40px_90px_-25px_rgba(0,0,0,.9)]"
                    >
                @else
                    <div class="relative z-[2] grid h-full w-full place-items-center bg-slab shadow-[0_40px_90px_-25px_rgba(0,0,0,.9)]">
                        <x-icons.logomark class="h-1/4 w-1/4 text-rule2" />
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
