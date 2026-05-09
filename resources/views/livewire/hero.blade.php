@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
@endphp

<section class="relative min-h-screen overflow-hidden pt-14 md:pt-20">
    <div class="absolute inset-0 bg-fixed bg-center bg-cover" style="background-image: url('/assets/img/music-bg.jpg')">
        <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/70 to-black/90"></div>
    </div>

    <div class="container relative px-4 pt-24 mx-auto">
        <div
            class="max-w-4xl mx-auto overflow-hidden text-center"
            x-data="{
                stages: { welcome: false, brand: false, subtitle: false, desc: false, buttons: false },
                init() {
                    const order = ['welcome', 'brand', 'subtitle', 'desc', 'buttons'];
                    order.forEach((key, i) => {
                        setTimeout(() => { this.stages[key] = true; }, 200 + i * 220);
                    });
                }
            }"
        >
            <div class="border-b rounded-lg shadow-lg bg-black/20 border-red-800/20 shadow-red-900/10">
                <!-- Main Title -->
                <div class="flex flex-wrap items-center justify-center text-5xl font-bold md:text-7xl gap-x-4">
                    <div
                        class="transform transition-all duration-700 ease-out"
                        :class="stages.welcome ? 'opacity-100 translate-x-0 scale-100' : 'opacity-0 -translate-x-24 scale-90'"
                    >
                        <span class="inline-block">Welcome to</span>
                    </div>

                    <div
                        class="text-transparent transform bg-clip-text bg-gradient-to-r from-red-800 to-red-500 transition-all duration-700 ease-out"
                        :class="stages.brand ? 'opacity-100 scale-100' : 'opacity-0 scale-50'"
                    >
                        Snow n Stuff
                    </div>
                </div>

                <!-- Subtitle -->
                <h2
                    class="mt-6 text-2xl font-light text-gray-300 md:text-3xl transform transition-all duration-700 ease-out"
                    :class="stages.subtitle ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                >
                    Music Management, Label and Music Production
                </h2>

                <!-- Description block -->
                <div
                    class="space-y-6 text-lg transform transition-all duration-700 ease-out"
                    :class="stages.desc ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-12'"
                >
                    <div class="flex flex-col gap-4">
                        <!-- Music Genres -->
                        <p class="mt-8 text-lg leading-relaxed text-gray-300">
                            Snow 'n' Stuff is releasing Tech House, Deep House, House and Techno.
                        </p>

                        <!-- Management Info -->
                        <div class="text-center">
                            <span class="text-red-500">Management for:</span>
                            <div class="flex flex-wrap justify-center gap-2 mt-2">
                                @foreach (['THK', 'G&S', 'Snow N Stuff', 'Style Da Kid'] as $name)
                                    <span class="px-3 py-1 text-sm transition-colors duration-300 rounded-md bg-white/5 hover:bg-white/10">
                                        {{ $name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Curator Info -->
                        <div class="mt-8">
                            <div class="flex items-center justify-center px-4 py-3 mx-auto space-x-3 text-gray-300 transition-all duration-300 rounded-lg bg-white/5 hover:bg-white/10 max-w-fit">
                                <x-icons.spotify class="w-6 h-6 text-green-500" />
                                <span class="text-sm font-medium md:text-base">
                                    Tastemaker & Curator of
                                    <span class="block md:inline">several Spotify playlists</span>
                                </span>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div
                            class="flex flex-col items-center justify-center gap-4 my-12 sm:flex-row transform transition-all duration-700 ease-out"
                            :class="stages.buttons ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                        >
                            <a href="#releases"
                                class="group relative px-6 py-2.5 w-full sm:w-auto overflow-hidden bg-gradient-to-r from-red-800 to-red-700 rounded-lg hover:from-red-700 hover:to-red-600 transition-all duration-300 {{ $focusRing }}">
                                <span class="relative z-10 font-medium text-white">Our Music</span>
                            </a>
                            <a href="#artists"
                                class="px-6 py-2.5 w-full sm:w-auto text-white font-medium border-2 border-red-800/50 rounded-lg hover:bg-red-800/20 transition-all duration-300 {{ $focusRing }}">
                                Our Artists
                            </a>
                            <a href="/blog"
                                class="px-6 py-2.5 w-full sm:w-auto text-white font-medium border-2 border-red-800/50 rounded-lg hover:bg-red-800/20 transition-all duration-300 {{ $focusRing }}">
                                Blog/Latest News
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
