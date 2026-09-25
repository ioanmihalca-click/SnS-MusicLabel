{{--
    Section heading of the redesigned pages: a hairline, the big display title
    with an optional count, an optional subtitle and, in the slot, a link or
    controls aligned to the right.
--}}
@props([
    'title',
    'count' => null,
    'subtitle' => null,
    'as' => 'h2',
])

<div {{ $attributes->class('mb-8 flex flex-wrap items-end justify-between gap-x-7 gap-y-[18px] border-t border-rule pt-5') }}>
    <div class="min-w-0">
        <{{ $as }} class="m-0 font-display text-[clamp(2.8rem,6.4vw,5.2rem)] font-extrabold uppercase leading-[.84] [overflow-wrap:anywhere]">
            {{ $title }}
            @if ($count !== null)
                <sup data-markdown-ignore class="relative top-[.4em] ml-1 align-top font-meta text-xs font-normal tracking-[.06em] text-mist">{{ $count }}</sup>
            @endif
        </{{ $as }}>

        @if (filled($subtitle))
            <p class="mt-2.5 max-w-[52ch] text-mist">{{ $subtitle }}</p>
        @endif
    </div>

    {{ $slot }}
</div>
