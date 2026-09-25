@php
    $monoLabel = 'm-0 mb-3.5 font-meta text-[11px] font-normal uppercase tracking-[.08em] text-mist';
    $gridClass = 'grid grid-cols-2 gap-x-3.5 gap-y-[26px] sm:grid-cols-3 sm:gap-x-[22px] sm:gap-y-9 lg:grid-cols-4';
@endphp

<x-layouts.site :seo="$seo">
    {{-- Hero: the artwork, blurred, is the page's colour --}}
    <section class="relative isolate overflow-hidden border-b border-rule">
        @if ($coverUrl)
            <div
                aria-hidden="true"
                data-markdown-ignore
                class="absolute -inset-[20%] -z-20 bg-cover bg-center opacity-55 blur-[90px] saturate-[1.4]"
                style="background-image: url('{{ $coverUrl }}')"
            ></div>
        @endif
        <div aria-hidden="true" data-markdown-ignore class="absolute inset-0 -z-10 bg-gradient-to-b from-ink/30 via-ink/75 via-70% to-ink"></div>

        <div class="site-wrap grid items-end gap-[clamp(28px,5vw,72px)] pb-[clamp(40px,6vw,72px)] pt-[clamp(32px,5vw,64px)] md:grid-cols-[minmax(0,.85fr)_minmax(0,1.15fr)]">
            <div class="relative w-full max-w-[520px]">
                @if ($coverUrl)
                    <img
                        src="{{ $coverUrl }}"
                        alt="Cover of {{ $release->title }}{{ filled($release->credit) ? ' by '.$release->credit : '' }}"
                        width="640"
                        height="640"
                        fetchpriority="high"
                        class="aspect-square w-full object-cover shadow-[0_40px_90px_-25px_rgba(0,0,0,.9)]"
                    >
                @else
                    <div class="grid aspect-square w-full place-items-center bg-slab">
                        <x-icons.logomark class="h-1/4 w-1/4 text-rule2" />
                    </div>
                @endif
            </div>

            <div class="relative flex min-w-0 flex-col gap-[18px]">
                <x-breadcrumbs :trail="$trail" />

                @php
                    $eyebrow = array_filter([$release->format?->getLabel(), $release->released_at?->format('d.m.Y')]);
                @endphp
                @if ($eyebrow !== [])
                    <p class="m-0 font-meta text-[11px] uppercase tracking-[.08em] text-mist">{{ implode(' · ', $eyebrow) }}</p>
                @endif

                <h1 class="m-0 font-display text-[clamp(3.2rem,8vw,7.4rem)] font-black uppercase leading-[.82] [overflow-wrap:anywhere]">{{ $release->title }}</h1>

                <x-release-credit :release="$release" class="m-0 text-xl font-medium" />

                <x-listen-links :release="$release" />

                <x-meta-list class="mt-2" :items="[
                    'Released' => $release->released_at?->format('d.m.Y'),
                    'Format' => implode(' · ', array_filter([
                        $release->format?->getLabel(),
                        $release->tracks->count() > 1 ? $release->tracks->count().' tracks' : null,
                    ])),
                    'Genre' => $release->genre?->getLabel(),
                    'Label' => \App\Support\Seo\SeoData::SITE_NAME,
                ]" />
            </div>
        </div>
    </section>

    {{-- Tracklist, story and support --}}
    <section class="site-wrap grid gap-[clamp(32px,5vw,80px)] pt-[clamp(40px,6vw,72px)] md:grid-cols-[minmax(0,1.1fr)_minmax(0,.9fr)]">
        <div>
            <h2 class="{{ $monoLabel }}">Tracklist</h2>

            @if ($release->tracks->isNotEmpty())
                <ol class="m-0 list-none border-t border-rule p-0">
                    @foreach ($release->tracks as $track)
                        <li class="grid grid-cols-[34px_minmax(0,1fr)_auto] items-center gap-3 border-b border-rule py-3.5">
                            <span data-markdown-ignore class="font-meta text-[10.5px] text-dim">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="min-w-0 font-semibold [overflow-wrap:anywhere]">{{ $track->title }}@if (filled($track->version)) <small class="block text-sm font-normal text-mist"><span class="sr-only">(</span>{{ $track->version }}<span class="sr-only">)</span></small>@endif</span>
                            @if ($track->duration)
                                <time datetime="{{ $track->isoDuration() }}" class="font-meta text-[11px] tabular-nums text-mist">{{ $track->duration }}</time>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="m-0 border-t border-rule pt-3.5 text-mist">The tracklist will be added soon.</p>
            @endif
        </div>

        <div class="flex flex-col gap-7">
            @if (filled($release->description))
                <div>
                    <h2 class="{{ $monoLabel }}">About</h2>
                    <div class="max-w-[62ch] space-y-4 text-frost [&_a]:underline [&_a]:underline-offset-4 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:m-0 [&_ul]:list-disc [&_ul]:pl-5">
                        {!! $release->description !!}
                    </div>
                </div>
            @endif

            @if (filled($release->chart_position) || filled($release->support))
                <div class="flex flex-col gap-3 border-t border-rule pt-4">
                    <h2 class="m-0 font-meta text-[11px] font-normal uppercase tracking-[.08em] text-mist">Support</h2>

                    @if (filled($release->chart_position))
                        <p class="m-0 flex items-baseline gap-3.5">
                            <strong class="font-display text-5xl font-black leading-[.9]">{{ $release->chart_position }}</strong>
                            <span>{{ $release->chart_name }}</span>
                        </p>
                    @endif

                    @if (filled($release->support))
                        <ul class="m-0 flex list-none flex-wrap gap-2 p-0" aria-label="Supported by">
                            @foreach ($release->support as $supporter)
                                <li class="border border-rule2 px-3 py-[9px] font-display text-[17px] font-bold uppercase leading-none tracking-[.02em]">{{ $supporter }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>
    </section>

    @if ($moreReleases->isNotEmpty())
        <section class="site-wrap pt-[clamp(64px,9vw,120px)]">
            <x-section-heading :title="'More from '.$mainArtist->name">
                <a href="{{ route('artists.show', $mainArtist->slug) }}" class="inline-flex items-center gap-2 border-b border-rule2 pb-[3px] font-semibold transition-colors hover:border-frost">Artist profile <span aria-hidden="true">→</span></a>
            </x-section-heading>

            <div class="{{ $gridClass }}">
                @foreach ($moreReleases as $moreRelease)
                    <x-release-card :release="$moreRelease" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
