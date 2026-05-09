@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
@endphp

<div id="artists" class="py-24 bg-black" data-reveal>
    <div class="container px-4 mx-auto">
        <x-section-header eyebrow="Our" title="Artists" />

        <!-- Artists Grid -->
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($artists as $artist)
                <div
                    x-data="{ expanded: false }"
                    class="group"
                    style="transition-delay: {{ 100 * $loop->iteration }}ms"
                >
                    <div class="relative p-6 overflow-hidden transition-all duration-300 rounded-xl bg-gradient-to-b from-gray-900 to-black hover:translate-y-[-4px] hover:shadow-2xl">
                        <!-- Artist Number -->
                        <div class="absolute text-6xl font-bold transition-colors duration-300 top-4 right-4 text-red-800/10 group-hover:text-red-800/20">
                            {{ str_pad($artist->order, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <!-- Artist Content -->
                        <div class="relative z-10">
                            <h4 class="mb-4 text-2xl font-bold text-white transition-colors duration-300 group-hover:text-red-800">
                                {{ $artist->name }}
                            </h4>

                            <!-- Artist Description -->
                            <div class="space-y-2">
                                <p class="text-gray-400 transition-colors duration-300 group-hover:text-gray-300">
                                    <span x-show="!expanded">{{ $artist->short_description }}</span>
                                    <span x-show="expanded" x-cloak>{{ $artist->plain_description }}</span>
                                </p>

                                @if ($artist->is_truncated)
                                    <button
                                        type="button"
                                        @click="expanded = !expanded"
                                        :aria-expanded="expanded.toString()"
                                        class="text-sm font-medium text-red-800 transition-colors duration-300 rounded hover:text-red-600 {{ $focusRing }}"
                                        x-text="expanded ? 'Read Less' : 'Read More'"
                                    >Read More</button>
                                @endif
                            </div>

                            <!-- Spotify Link -->
                            <div class="flex items-center justify-between mt-6">
                                <a href="{{ $artist->spotify_url }}"
                                    target="_blank"
                                    rel="noreferrer"
                                    aria-label="Listen to {{ $artist->name }} on Spotify"
                                    class="inline-flex items-center space-x-2 text-gray-400 transition-colors duration-300 rounded hover:text-green-500 {{ $focusRing }}">
                                    <x-icons.spotify class="w-6 h-6" />
                                    <span>Listen on Spotify</span>
                                </a>
                            </div>
                        </div>

                        <!-- Decorative Elements -->
                        <div class="absolute top-0 left-0 w-32 h-32 transition-opacity duration-300 rounded-br-full opacity-50 bg-gradient-to-br from-red-800/10 to-transparent group-hover:opacity-100"></div>
                        <div class="absolute bottom-0 right-0 w-32 h-32 transition-opacity duration-300 rounded-tl-full opacity-50 bg-gradient-to-tl from-red-800/10 to-transparent group-hover:opacity-100"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
