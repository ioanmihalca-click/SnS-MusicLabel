@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
@endphp

<div id="releases" class="py-24 bg-black/95" data-reveal>
    <div class="container px-4 mx-auto">
        <x-section-header eyebrow="Releases" title="Check Our Releases" />

        <!-- Releases Grid -->
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($releases as $release)
                <div
                    x-data="{ expanded: false }"
                    class="p-6 transition-all duration-500 group rounded-xl bg-gradient-to-b from-gray-900/50 to-black border border-white/5 hover:border-red-800/30 hover:translate-y-[-6px] hover:shadow-[var(--sh-card-hover)]"
                >
                    @if ($release->title)
                        <div class="mb-4">
                            <p class="text-[0.65rem] uppercase tracking-[0.3em] text-red-500 font-semibold">Release</p>
                            <h3 class="mt-1 font-display font-black uppercase tracking-tight leading-none text-white transition-colors duration-300 group-hover:text-red-500"
                                style="font-size: clamp(1.5rem, 2.5vw, 2rem);">
                                {{ $release->title }}
                            </h3>
                        </div>
                    @endif

                    <!-- Spotify Embed -->
                    <div class="relative mb-6 overflow-hidden rounded-lg">
                        {!! $release->spotify_embed_code !!}
                    </div>

                    <!-- Description -->
                    @if ($release->plain_description !== '')
                        <div class="relative overflow-hidden">
                            <div class="p-4 space-y-2 text-gray-300 border bg-gray-900/50 rounded-xl backdrop-blur-sm border-gray-800/50">
                                <p>
                                    <span x-show="!expanded">{{ $release->short_description }}</span>
                                    <span x-show="expanded" x-cloak>{{ $release->plain_description }}</span>
                                </p>

                                @if ($release->is_truncated)
                                    <button
                                        type="button"
                                        @click="expanded = !expanded"
                                        :aria-expanded="expanded.toString()"
                                        class="text-sm font-medium text-red-800 transition-colors duration-300 rounded hover:text-red-600 {{ $focusRing }}"
                                        x-text="expanded ? 'Read Less' : 'Read More'"
                                    >Read More</button>
                                @endif
                            </div>

                            <!-- Decorative Elements -->
                            <div class="absolute top-0 left-0 w-24 h-24 transition-opacity duration-500 rounded-br-full opacity-0 bg-gradient-to-br from-red-800/10 to-transparent group-hover:opacity-100"></div>
                            <div class="absolute bottom-0 right-0 w-24 h-24 transition-opacity duration-500 rounded-tl-full opacity-0 bg-gradient-to-tl from-red-800/10 to-transparent group-hover:opacity-100"></div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
