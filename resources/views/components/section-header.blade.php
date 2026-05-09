@props([
    'eyebrow' => null,
    'title' => null,
    'gradient' => false,
    'titleClass' => null,
])

<div {{ $attributes->merge(['class' => 'mb-16 text-center']) }}>
    @if ($eyebrow)
        <h2 class="mb-4 text-xs md:text-sm tracking-[0.4em] text-red-500 uppercase font-semibold">{{ $eyebrow }}</h2>
    @endif
    @if ($title)
        @if ($gradient)
            <p class="font-display font-black uppercase tracking-tight leading-[0.9] text-transparent bg-clip-text bg-gradient-to-r from-red-700 via-red-500 to-red-300 {{ $titleClass }}"
               style="font-size: clamp(2.5rem, 6vw, 4.5rem);">
                {{ $title }}
            </p>
        @else
            <p class="font-display font-black uppercase tracking-tight leading-[0.9] text-white {{ $titleClass }}"
               style="font-size: clamp(2.5rem, 6vw, 4.5rem);">
                {{ $title }}
            </p>
        @endif
    @endif
</div>
