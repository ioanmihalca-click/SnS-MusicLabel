@php
    $managementNames = ['THK', 'G&S', "Snow 'n' Stuff", 'Style Da Kid'];
@endphp

<section class="relative min-h-screen overflow-hidden pt-14 md:pt-20" data-hero>
    <div
        class="absolute inset-0 bg-fixed bg-center bg-cover will-change-transform"
        style="background-image: url('/assets/img/music-bg.jpg')"
        data-hero-bg
    >
        <div class="absolute inset-0 bg-gradient-to-b from-black/85 via-black/75 to-black/95"></div>
    </div>

    <div class="container relative px-4 pt-24 pb-12 mx-auto">
        <div
            class="max-w-5xl mx-auto text-center"
            x-data="{
                stages: { welcome: false, brand: false, subtitle: false, desc: false, featuredTrack: false },
                init() {
                    const order = ['welcome', 'brand', 'subtitle', 'desc', 'featuredTrack'];
                    order.forEach((key, i) => {
                        setTimeout(() => { this.stages[key] = true; }, 200 + i * 220);
                    });
                }
            }"
        >
            {{-- Eyebrow --}}
            <p
                class="text-xs md:text-sm uppercase text-gray-500 tracking-[0.4em] transform transition-all duration-700 ease-out"
                :class="stages.welcome ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2'"
            >
                Welcome to
            </p>

            {{-- Brand wordmark --}}
            <h1
                class="mt-3 md:mt-4 font-display font-black uppercase leading-[0.82] tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-red-700 via-red-500 to-red-300 transition-all duration-1000 ease-out"
                style="font-size: clamp(3.5rem, 13vw, 9.5rem);"
                :class="stages.brand ? 'opacity-100 scale-100' : 'opacity-0 scale-90'"
            >
                Snow 'n' Stuff
            </h1>

            {{-- Subtitle --}}
            <p
                class="mt-6 md:mt-8 text-lg md:text-2xl font-light text-gray-200 transform transition-all duration-700 ease-out"
                :class="stages.subtitle ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3'"
            >
                Music Management, Label and Music Production
            </p>

            {{-- Description block --}}
            <div
                class="mt-10 md:mt-12 transform transition-all duration-700 ease-out"
                :class="stages.desc ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
            >
                {{-- Genres --}}
                <p class="text-sm md:text-base font-medium text-gray-400 tracking-wider">
                    Tech House <span class="text-red-700/80">·</span>
                    Deep House <span class="text-red-700/80">·</span>
                    House <span class="text-red-700/80">·</span>
                    Techno
                </p>

                {{-- Management strip --}}
                <div class="mt-10 md:mt-12 flex items-center justify-center gap-3 md:gap-4 text-gray-500" aria-label="Management for">
                    <span class="h-px w-8 md:w-16 bg-gradient-to-r from-transparent to-red-700/60" aria-hidden="true"></span>
                    <span class="text-[0.65rem] md:text-xs font-semibold tracking-[0.4em] uppercase text-red-500">Management</span>
                    <span class="h-px w-8 md:w-16 bg-gradient-to-l from-transparent to-red-700/60" aria-hidden="true"></span>
                </div>

                <p class="mt-4 flex flex-wrap items-center justify-center gap-x-3 gap-y-2 text-base md:text-lg font-medium text-gray-200">
                    @foreach ($managementNames as $name)
                        <span>{{ $name }}</span>
                        @if (! $loop->last)
                            <span class="text-red-700/60" aria-hidden="true">·</span>
                        @endif
                    @endforeach
                </p>

                {{-- Featured Track pill — primary CTA, replaces the redundant nav-duplicating buttons --}}
                <div
                    class="mt-12 md:mt-16 transform transition-all duration-700 ease-out"
                    :class="stages.featuredTrack ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                >
                    <livewire:featured-track />
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 hidden md:flex flex-col items-center gap-2 text-gray-500">
        <span class="text-[0.6rem] uppercase tracking-[0.4em]">Scroll</span>
        <span class="block h-10 w-px bg-gradient-to-b from-red-700/60 to-transparent" aria-hidden="true"></span>
    </div>
</section>
