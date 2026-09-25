{{--
    A release's credit with the roster artists linked to their pages:
    "THK & Pacha Man" links THK. Renders nothing without a credit.
    Expects the `artists` relation to be loaded.
--}}
@props([
    'release',
    'as' => 'p',
])

@if (filled($release->credit))
    {{-- Built in one piece: whitespace between the parts would show before the commas --}}
    @php
        $creditHtml = collect($release->creditParts())
            ->map(fn (array $part): string => $part['artist']
                ? '<a href="'.e(route('artists.show', $part['artist']->slug)).'" class="border-b border-rule2 transition-colors hover:border-frost">'.e($part['text']).'</a>'
                : e($part['text']))
            ->implode('');
    @endphp
    <{{ $as }} {{ $attributes }}>{!! $creditHtml !!}</{{ $as }}>
@endif
