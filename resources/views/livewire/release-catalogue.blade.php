<div class="site-wrap pt-[clamp(32px,5vw,64px)]">
    <x-breadcrumbs :trail="$trail" class="mb-6" />

    <x-section-heading as="h1" title="Releases" :count="$releases->count()" subtitle="Every release on the label, newest first." />

    <div data-markdown-ignore role="group" aria-label="Filter releases" class="-mt-2 mb-9 flex flex-wrap items-end gap-3">
        @foreach ($filters as $property => $filter)
            <label class="flex min-w-[150px] flex-1 flex-col gap-[7px] sm:flex-none">
                <span class="font-meta text-[10px] uppercase tracking-[.08em] text-mist">{{ $filter['label'] }}</span>
                <span class="relative">
                    <select
                        wire:model.live="{{ $property }}"
                        class="w-full cursor-pointer appearance-none rounded-full border border-rule2 bg-ink py-2.5 pl-4 pr-10 text-sm text-frost transition-colors hover:border-mist focus:border-frost"
                    >
                        <option value="">{{ $filter['all'] }}</option>
                        @foreach ($filter['options'] as $value => $label)
                            <option value="{{ $value }}" @selected((string) $value === $this->{$property})>{{ $label }}</option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-4 top-1/2 h-3 w-3 -translate-y-1/2 text-mist" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2.5 4.5L6 8l3.5-3.5" /></svg>
                </span>
            </label>
        @endforeach

        @if ($isFiltered)
            <button
                type="button"
                wire:click="clearFilters"
                class="rounded-full px-2 py-2.5 text-sm font-semibold text-frost underline decoration-rule2 underline-offset-4 transition-colors hover:decoration-frost"
            >Clear filters</button>
        @endif
    </div>

    <div wire:loading.class="opacity-60" class="transition-opacity">
        @if ($releases->isNotEmpty())
            <div class="grid grid-cols-2 gap-x-3.5 gap-y-[26px] sm:grid-cols-3 sm:gap-x-[22px] sm:gap-y-9 lg:grid-cols-4">
                @foreach ($releases as $release)
                    <x-release-card :release="$release" wire:key="release-{{ $release->id }}" />
                @endforeach
            </div>
        @else
            <p class="border-t border-rule pt-5 text-mist">No releases match these filters.</p>
        @endif
    </div>
</div>
