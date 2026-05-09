<section
    class="relative border-y border-white/5 bg-black"
    aria-label="Snow 'n' Stuff at a glance"
    data-reveal
>
    <div class="container px-4 py-12 md:py-16 mx-auto">
        <ul
            class="grid grid-cols-2 md:grid-cols-5 gap-y-10 gap-x-4 md:gap-x-8 items-end"
            x-data="{
                shown: false,
                values: {{ \Illuminate\Support\Js::from(array_column($stats, 'value')) }},
                animated: [],
                init() {
                    this.animated = this.values.map(() => 0);
                    const io = new IntersectionObserver((entries) => {
                        for (const e of entries) {
                            if (e.isIntersecting && !this.shown) {
                                this.shown = true;
                                this.runCounts();
                                io.disconnect();
                            }
                        }
                    }, { threshold: 0.4 });
                    io.observe(this.$el);
                },
                runCounts() {
                    const duration = 1200;
                    const start = performance.now();
                    const tick = (now) => {
                        const t = Math.min(1, (now - start) / duration);
                        const ease = 1 - Math.pow(1 - t, 3);
                        this.animated = this.values.map((v) => Math.round(v * ease));
                        if (t < 1) requestAnimationFrame(tick);
                        else this.animated = [...this.values];
                    };
                    requestAnimationFrame(tick);
                }
            }"
        >
            @foreach ($stats as $i => $stat)
                <li class="text-center md:text-left">
                    <p
                        class="font-display font-black uppercase leading-none tracking-tight text-transparent bg-clip-text bg-gradient-to-b from-white via-gray-200 to-gray-500"
                        style="font-size: clamp(2.5rem, 6vw, 4.5rem);"
                    >
                        @if (! ($stat['noPad'] ?? false))
                            <span x-text="String(animated[{{ $i }}]).padStart(2, '0')">{{ str_pad($stat['value'], 2, '0', STR_PAD_LEFT) }}</span>
                        @else
                            <span x-text="animated[{{ $i }}]">{{ $stat['value'] }}</span>
                        @endif
                    </p>
                    <p class="mt-2 text-[0.65rem] md:text-xs uppercase tracking-[0.3em] text-red-500/80 font-semibold">
                        {{ $stat['label'] }}
                    </p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
