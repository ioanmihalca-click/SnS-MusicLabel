<div id="artists" class="py-24 bg-black">
    <div class="container px-4 mx-auto">
        <!-- Section Header -->
        <div class="mb-16 text-center">
            <h2 class="mb-3 text-sm tracking-wider text-gray-400 uppercase">Our</h2>
            <p class="text-4xl font-bold text-red-800">Artists</p>
        </div>

        <!-- Artists Grid -->
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($artists as $artist)
                <div 
                    x-data="{ hover: false }"
                    @mouseenter="hover = true"
                    @mouseleave="hover = false"
                    class="group"
                    x-show="true"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    style="transition-delay: {{ 100 * $loop->iteration }}ms"
                >
                    <a href="{{ $artist->spotify_url }}" 
                       target="_blank" 
                       rel="noreferrer"
                       class="block relative overflow-hidden rounded-xl bg-gradient-to-b from-gray-900 to-black p-6 transition-all duration-300 hover:translate-y-[-4px] hover:shadow-2xl"
                    >
                        <!-- Artist Number -->
                        <div class="absolute text-6xl font-bold transition-colors duration-300 top-4 right-4 text-red-800/10 group-hover:text-red-800/20">
                            {{ str_pad($artist->order, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <!-- Artist Content -->
                        <div class="relative z-10">
                            <!-- Artist Name -->
                            <h4 class="mb-4 text-2xl font-bold text-white transition-colors duration-300 group-hover:text-red-800">
                                {{ $artist->name }}
                            </h4>

                            <!-- Artist Description -->
                            <p class="text-gray-400 transition-colors duration-300 group-hover:text-gray-300">
                                {!! $artist->description !!}
                            </p>

                            <!-- Spotify Icon -->
                            <div class="flex justify-end mt-6">
                                <svg class="w-6 h-6 text-gray-400 transition-colors duration-300 group-hover:text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Decorative Elements -->
                        <div class="absolute top-0 left-0 w-32 h-32 transition-opacity duration-300 rounded-br-full opacity-50 bg-gradient-to-br from-red-800/10 to-transparent group-hover:opacity-100"></div>
                        <div class="absolute bottom-0 right-0 w-32 h-32 transition-opacity duration-300 rounded-tl-full opacity-50 bg-gradient-to-tl from-red-800/10 to-transparent group-hover:opacity-100"></div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>