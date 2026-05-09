{{--
    Branded About composition. Mirrors the top-bar look (hexagon + wordmark)
    scaled up — same brand-DNA, no extra decoration. Decorative corners
    around it stay as section-level framing (defined in about.blade.php).
--}}
<div
    class="relative aspect-square w-full overflow-hidden rounded-lg shadow-2xl shadow-black/60 transition-transform duration-700 ease-out group-hover:scale-[1.02]"
>
    {{-- Base gradient field --}}
    <div class="absolute inset-0 bg-gradient-to-br from-gray-950 via-black to-red-950/30"></div>

    {{-- Soft radial red glow --}}
    <div
        class="absolute inset-0"
        style="background: radial-gradient(circle at 50% 50%, rgba(127, 29, 29, 0.35) 0%, rgba(0, 0, 0, 0) 65%);"
        aria-hidden="true"
    ></div>

    {{-- Logomark + wordmark, stacked, centered --}}
    <div class="absolute inset-0 flex flex-col items-center justify-center gap-6 px-6">
        <x-icons.logomark
            class="w-1/3 h-1/3 text-red-500 drop-shadow-[0_0_28px_rgba(220,38,38,0.4)] transition-transform duration-[2000ms] ease-out group-hover:rotate-180"
            aria-hidden="false"
            title="Snow 'n' Stuff"
        />

        <p
            class="font-display font-black uppercase tracking-tight leading-none text-transparent bg-clip-text bg-gradient-to-b from-white to-gray-400 text-center"
            style="font-size: clamp(2rem, 6vw, 4rem);"
        >
            <x-brand-name uppercase />
        </p>

        <p class="text-[0.65rem] uppercase tracking-[0.4em] text-red-500/70 font-semibold">
            Music Label <span class="text-gray-600 mx-2">·</span> Est. 2020
        </p>
    </div>
</div>
