{{--
    A news entry (App\Support\NewsItem): image, "type · date" and the title,
    linked to the post or the release. `withSummary` adds the post's summary.
--}}
@props([
    'item',
    'headingLevel' => 'h3',
    'withSummary' => false,
])

<article {{ $attributes->class('group flex min-w-0 flex-col gap-3') }}>
    <a href="{{ $item->url }}" tabindex="-1" aria-hidden="true" data-markdown-ignore class="block aspect-[16/10] overflow-hidden bg-slab">
        @if ($item->imageUrl)
            <img
                src="{{ $item->imageUrl }}"
                alt=""
                width="640"
                height="400"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(.2,.7,.1,1)] group-hover:scale-[1.04]"
            >
        @else
            <span class="grid h-full w-full place-items-center bg-gradient-to-br from-slab2 to-slab">
                <x-icons.logomark class="h-16 w-16 text-signal" />
            </span>
        @endif
    </a>

    <p class="m-0 flex gap-2.5 font-meta text-[10px] uppercase tracking-[.08em] text-dim">
        <span class="text-frost">{{ $item->label() }}</span>
        <time datetime="{{ $item->date->toDateString() }}">{{ $item->date->format('d.m.Y') }}</time>
    </p>

    <{{ $headingLevel }} class="m-0 text-[17px] font-semibold leading-[1.3] [overflow-wrap:anywhere]">
        <a href="{{ $item->url }}" class="underline-offset-[3px] hover:underline group-hover:underline">{{ $item->title }}</a>
    </{{ $headingLevel }}>

    @if ($withSummary && filled($item->summary))
        <p class="m-0 line-clamp-3 text-[15px] text-mist">{{ $item->summary }}</p>
    @endif
</article>
