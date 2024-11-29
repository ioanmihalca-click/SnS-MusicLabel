<div id="releases" class="py-24 bg-black/95">
    <div class="container px-4 mx-auto">
        <!-- Section Header -->
        <div class="mb-16 text-center">
            <h2 class="mb-3 text-sm tracking-wider text-gray-400 uppercase">Releases</h2>
            <p class="text-4xl font-bold text-red-800">Check Our Releases</p>
        </div>

        <!-- Releases Grid -->
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($releases as $release)
                <div 
                    x-data="{ 
                        hover: false,
                        expanded: false,
                        get shortDescription() {
                            const fullText = this.$refs.fullDescription.textContent;
                            return fullText.length > 150 ? fullText.substring(0, 150) + '...' : fullText;
                        }
                    }"
                    @mouseenter="hover = true"
                    @mouseleave="hover = false"
                    class="p-6 transition-all duration-300 group rounded-xl bg-gradient-to-b from-gray-900/50 to-black hover:shadow-2xl hover:shadow-red-800/10"
                >
                    <!-- Spotify Embed -->
                    <div class="relative mb-6 overflow-hidden rounded-lg">
                        {!! $release->spotify_embed_code !!}
                    </div>

                    <!-- Description -->
                    @if($release->description)
                        <div class="relative overflow-hidden">
                            <div x-ref="fullDescription" class="hidden">{!! $release->description !!}</div>
                            
                            <div class="p-4 space-y-2 text-gray-300 border bg-gray-900/50 rounded-xl backdrop-blur-sm border-gray-800/50">
                                <p x-text="expanded ? $refs.fullDescription.textContent : shortDescription"></p>
                                
                                <button 
                                    @click="expanded = !expanded"
                                    x-show="$refs.fullDescription.textContent.length > 150"
                                    class="text-sm font-medium text-red-800 transition-colors duration-300 hover:text-red-600 focus:outline-none"
                                    x-text="expanded ? 'Read Less' : 'Read More'">
                                </button>
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