{{--
    "Supported & played by": the DJs from the releases' support lists, then
    the chart positions, each linked to its release.
--}}
<section aria-labelledby="support-title" class="mt-[clamp(64px,9vw,120px)] border-y border-rule bg-slab">
    <div class="site-wrap py-[clamp(48px,7vw,88px)]">
        <h2 id="support-title" class="m-0 font-meta text-[11px] font-normal uppercase tracking-[.08em] text-mist">Supported &amp; played by</h2>

        @if ($supporters !== [])
            <ul class="m-0 mt-[18px] flex max-w-[1100px] list-none flex-wrap p-0 font-display text-[clamp(2rem,4.6vw,3.9rem)] font-bold uppercase leading-[1.02]">
                @foreach ($supporters as $supporter)
                    <li @class([
                        'whitespace-nowrap',
                        "after:px-[.18em] after:font-semibold after:text-dim after:content-['/']" => ! $loop->last,
                    ])>{{ $supporter }}</li>
                @endforeach
            </ul>
        @endif

        @if ($charts->isNotEmpty())
            <ul class="m-0 mt-[clamp(40px,5vw,64px)] grid list-none grid-cols-2 border-t border-rule2 p-0 md:grid-cols-4">
                @foreach ($charts as $release)
                    <li @class([
                        'flex flex-col gap-1.5 pr-[22px] pt-[22px]',
                        'border-l border-rule2 pl-[22px]' => ! $loop->first,
                        'max-md:border-l-0 max-md:pl-0' => $loop->index === 2,
                        'max-md:mt-[22px]' => $loop->index >= 2,
                    ])>
                        <span class="font-display text-[clamp(2.6rem,4.6vw,3.8rem)] font-black leading-[.9]">{{ $release->chart_position }}</span>
                        @if (filled($release->chart_name))
                            <span class="font-semibold">{{ $release->chart_name }}</span>
                        @endif
                        <a href="{{ route('releases.show', $release->slug) }}" class="font-meta text-[10px] uppercase tracking-[.08em] text-mist transition-colors hover:text-frost">{{ implode(' · ', array_filter([$release->title, $release->released_at?->year])) }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
