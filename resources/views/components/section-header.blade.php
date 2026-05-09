@props([
    'eyebrow' => null,
    'title' => null,
    'gradient' => false,
    'titleClass' => null,
])

<div {{ $attributes->merge(['class' => 'mb-16 text-center']) }}>
    @if ($eyebrow)
        <h2 class="mb-3 text-sm tracking-wider text-gray-400 uppercase">{{ $eyebrow }}</h2>
    @endif
    @if ($title)
        @if ($gradient)
            <p class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-800 to-red-500 {{ $titleClass }}">
                {{ $title }}
            </p>
        @else
            <p class="text-4xl font-bold text-red-800 {{ $titleClass }}">
                {{ $title }}
            </p>
        @endif
    @endif
</div>
