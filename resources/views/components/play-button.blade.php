{{--
    Plays a release or a playlist in the footer player (resources/js/player.js).
    The button carries what the player loads and shows: the Spotify URI, the
    title, the credit and the page it links back to. Renders nothing without
    a URI. Without a `label` it is a round icon button; with one, a pill like
    x-site.button whose visible label switches to "Pause" while it plays. The
    player keeps aria-pressed and data-state ("playing" / "paused") in sync
    while its URI is loaded.
--}}
@props([
    'uri',
    'title',
    'url',
    'credit' => null,
    'label' => null,
    'primary' => false,
])

@if (filled($uri))
    @php
        $name = $title.(filled($credit) ? " by {$credit}" : '');
        // "Play Speak To Me by G&S"; a longer label keeps its own words: "Play latest: Speak To Me by G&S".
        $hiddenText = match ($label) {
            null => "Play {$name}",
            'Play' => " {$name}",
            default => ": {$name}",
        };
    @endphp

    <button
        type="button"
        data-play="{{ $uri }}"
        data-play-title="{{ $title }}"
        @if (filled($credit)) data-play-credit="{{ $credit }}" @endif
        data-play-url="{{ $url }}"
        aria-pressed="false"
        {{ $attributes->class([
            'group/play inline-flex flex-none items-center justify-center rounded-full border transition-colors',
            'h-[34px] w-[34px] border-rule2 text-frost hover:border-frost data-[state=playing]:border-frost data-[state=playing]:bg-frost data-[state=playing]:text-ink [&_svg]:h-3.5 [&_svg]:w-3.5' => $label === null,
            'gap-2.5 whitespace-nowrap px-[22px] py-3.5 text-sm font-semibold leading-none tracking-[.01em] [&_svg]:h-4 [&_svg]:w-4' => $label !== null,
            'border-frost bg-frost text-ink hover:bg-white' => $label !== null && $primary,
            'border-rule2 text-frost hover:border-frost' => $label !== null && ! $primary,
        ]) }}
    >
        <svg class="block group-data-[state=playing]/play:hidden" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z" /></svg>
        <svg class="hidden group-data-[state=playing]/play:block" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 5h4v14H6zM14 5h4v14h-4z" /></svg>
        @if ($label !== null)
            {{-- The visible label switches to "Pause" while playing; the accessible name stays constant and aria-pressed carries the state (WAI-ARIA toggle button). --}}
            <span aria-hidden="true" class="group-data-[state=playing]/play:hidden">{{ $label }}</span>
            <span aria-hidden="true" class="hidden group-data-[state=playing]/play:inline">Pause</span>
            <span class="sr-only">{{ $label }}{{ $hiddenText }}</span>
        @else
            <span class="sr-only">{{ $hiddenText }}</span>
        @endif
    </button>
@endif
