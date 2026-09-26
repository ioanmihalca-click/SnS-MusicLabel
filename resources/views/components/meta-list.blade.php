{{--
    Key facts as a definition list, e.g. ['Released' => '04.04.2025', 'Format' => 'Single'].
    Empty values are skipped.
--}}
@props([
    'items',
])

<dl {{ $attributes->class('grid grid-cols-[repeat(auto-fit,minmax(130px,1fr))] border-t border-frost/15') }}>
    @foreach ($items as $label => $value)
        @continue(blank($value))

        <div class="pr-3.5 pt-3.5">
            <dt class="font-meta text-[10px] uppercase tracking-[.08em] text-dim">{{ $label }}</dt>
            <dd class="mt-1.5 font-semibold tabular-nums">{{ $value }}</dd>
        </div>
    @endforeach
</dl>
