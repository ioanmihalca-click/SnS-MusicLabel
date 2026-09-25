{{--
    Pill-shaped link button of the redesigned pages. `primary` is the filled,
    light variant; external links open in a new tab.
--}}
@props([
    'href',
    'primary' => false,
    'external' => false,
])

<a
    href="{{ $href }}"
    @if ($external) target="_blank" rel="noopener" @endif
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-2.5 whitespace-nowrap rounded-full border px-[22px] py-3.5 text-sm font-semibold leading-none tracking-[.01em] transition-colors [&_svg]:h-4 [&_svg]:w-4',
        'border-frost bg-frost text-ink hover:bg-white' => $primary,
        'border-rule2 text-frost hover:border-frost' => ! $primary,
    ]) }}
>{{ $slot }}</a>
