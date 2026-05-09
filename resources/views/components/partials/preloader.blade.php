{{--
    Branded preloader. The 3 generic concentric rings have been replaced by a
    composition that reinforces SnS identity: the hexagonal logomark breathing
    at the centre, a thin red orbit ring rotating around it, an equalizer
    strip below, and the wordmark in display font as a final flourish.

    Auto-dismisses after 1 s on Alpine init. `prefers-reduced-motion` neutralises
    the pulse / orbit / equalizer via CSS, so users get a still composition.
--}}
<div
    x-data="{ loading: true }"
    x-init="setTimeout(() => loading = false, 1000)"
    x-show="loading"
    x-transition:leave="transition-opacity duration-700 ease-out"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    role="status"
    aria-live="polite"
    aria-label="Loading"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm"
>
    <div class="flex flex-col items-center gap-6">
        {{-- Logomark + orbit ring --}}
        <div class="relative grid w-28 h-28 place-items-center">
            <span
                class="absolute inset-0 rounded-full border border-transparent border-t-red-500/70 border-r-red-700/30 animate-brand-orbit"
                aria-hidden="true"
            ></span>
            <x-icons.logomark
                class="w-20 h-20 text-red-500 animate-brand-pulse"
                aria-hidden="true"
            />
        </div>

        {{-- Equalizer strip --}}
        <div class="flex items-end gap-1 h-6" aria-hidden="true">
            <span class="eq-bar eq-bar-1 w-1 bg-red-500 rounded-sm"></span>
            <span class="eq-bar eq-bar-2 w-1 bg-red-500 rounded-sm"></span>
            <span class="eq-bar eq-bar-3 w-1 bg-red-500 rounded-sm"></span>
            <span class="eq-bar eq-bar-4 w-1 bg-red-500 rounded-sm"></span>
            <span class="eq-bar eq-bar-5 w-1 bg-red-500 rounded-sm"></span>
        </div>

        {{-- Wordmark --}}
        <p class="font-display font-black uppercase tracking-[0.3em] text-xs text-gray-500">
            <x-brand-name uppercase />
        </p>
    </div>
</div>
