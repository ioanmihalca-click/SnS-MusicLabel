<div id="releases" class="relative py-32 bg-black overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute inset-0 z-0">
        <div class="absolute top-1/2 left-1/2 w-full h-full bg-gradient-radial from-red-900/8 via-transparent to-transparent transform -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
        <div class="absolute top-0 left-0 w-[400px] h-[400px] bg-red-900/10 rounded-full blur-[150px] animate-pulse-soft"></div>
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-red-800/8 rounded-full blur-[150px] animate-pulse-soft" style="animation-delay: 2s;"></div>
    </div>

    <div class="container relative px-4 mx-auto z-10">
        <!-- Section Header -->
        <div class="mb-20 flex flex-col items-start md:flex-row md:items-end md:justify-between pb-8" 
             style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <div>
                <span class="badge-primary mb-3">
                    <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Catalog & Discography
                </span>
                <h2 class="text-5xl md:text-7xl font-bold font-display text-white tracking-tighter">
                    LATEST <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-red-500 to-red-800">RELEASES</span>
                </h2>
            </div>
            <div class="mt-8 md:mt-0 max-w-sm text-gray-400 text-right hidden md:block">
                <p class="text-sm leading-relaxed">Explore our latest tracks available on all major streaming platforms.</p>
            </div>
        </div>

        <!-- Releases Grid -->
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-3">
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
                    class="group relative"
                >
                    <!-- Main Card -->
                    <div class="relative rounded-2xl p-[1px] transition-all duration-500 group-hover:-translate-y-2"
                         style="background: linear-gradient(145deg, rgba(60, 60, 60, 0.2), rgba(30, 30, 30, 0.1));">
                        
                        <div class="relative rounded-2xl overflow-hidden"
                             style="
                                background: linear-gradient(145deg, rgba(20, 20, 20, 0.98), rgba(10, 10, 10, 0.99));
                                box-shadow: 
                                    0 4px 20px -5px rgba(0, 0, 0, 0.5),
                                    0 2px 8px -2px rgba(0, 0, 0, 0.3),
                                    inset 0 1px 0 0 rgba(255, 255, 255, 0.02);
                             ">
                            
                            <!-- Top Label -->
                            <div class="flex justify-between items-center px-5 py-3"
                                 style="border-bottom: 1px solid rgba(255, 255, 255, 0.04); background: linear-gradient(180deg, rgba(25, 25, 25, 0.8), rgba(15, 15, 15, 0.9));">
                                <div class="flex space-x-1.5">
                                    <div class="w-1.5 h-4 rounded-sm" style="background: linear-gradient(180deg, #dc2626, #991b1b);"></div>
                                    <div class="w-1.5 h-4 bg-white/10 rounded-sm"></div>
                                    <div class="w-1.5 h-4 bg-white/10 rounded-sm"></div>
                                </div>
                                <span class="text-[0.65rem] uppercase tracking-[0.2em] text-gray-500 font-mono">SNOW 'N' STUFF // REC</span>
                            </div>

                            <!-- Main Content Area -->
                            <div class="p-5">
                                <!-- Spotify Embed Wrapper -->
                                <div class="relative mb-5 overflow-hidden rounded-xl transition-all duration-500"
                                     style="
                                        box-shadow: 0 8px 30px -10px rgba(0, 0, 0, 0.5);
                                        border: 1px solid rgba(255, 255, 255, 0.05);
                                     ">
                                    {!! $release->spotify_embed_code !!}
                                    
                                    <!-- Shine Effect -->
                                    <div class="absolute inset-0 bg-gradient-to-tr from-white/[0.03] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                                </div>

                                <!-- Description Module -->
                                @if($release->description)
                                    <div class="relative overflow-hidden mt-5 pt-5" style="border-top: 1px dashed rgba(255, 255, 255, 0.08);">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-[0.15em] mb-3 flex items-center gap-2">
                                            <span class="w-4 h-[1px] bg-red-600/50"></span>
                                            Editor's Note
                                        </h4>
                                        
                                        <div class="relative">
                                            <div x-ref="fullDescription" class="hidden">{!! $release->description !!}</div>
                                            
                                            <div class="space-y-2 text-sm leading-relaxed text-gray-400 font-light">
                                                <p x-html="expanded ? $refs.fullDescription.innerHTML : shortDescription"></p>
                                            </div>

                                            <button 
                                                @click="expanded = !expanded"
                                                x-show="$refs.fullDescription.textContent.length > 150"
                                                class="mt-4 text-xs font-bold uppercase tracking-widest transition-all duration-300 flex items-center gap-2"
                                                :class="expanded ? 'text-gray-500 hover:text-white' : 'text-red-500 hover:text-red-400'"
                                            >
                                                <span x-text="expanded ? 'COLLAPSE' : 'EXPAND'"></span>
                                                <svg class="w-3 h-3 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                            <div x-show="!expanded" class="absolute bottom-0 left-0 w-full h-10 bg-gradient-to-t from-[#0a0a0a] to-transparent pointer-events-none"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Bottom Bar -->
                            <div class="h-1.5 rounded-b-2xl overflow-hidden"
                                 style="background: linear-gradient(90deg, rgba(220, 38, 38, 0.3), rgba(127, 29, 29, 0.2), rgba(220, 38, 38, 0.3));">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Glow Behind -->
                    <div class="absolute -inset-2 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-700 pointer-events-none -z-10"
                         style="background: radial-gradient(ellipse at center, rgba(220, 38, 38, 0.15) 0%, transparent 70%);"></div>
                </div>
            @endforeach
        </div>
        
        <!-- View All Button -->
        <div class="mt-20 text-center">
            <a href="https://open.spotify.com/search/snow%20n%20stuff/tracks" 
               target="_blank" 
               class="group inline-flex items-center gap-4 px-8 py-4 text-sm font-bold tracking-widest uppercase transition-all duration-300 rounded-full"
               style="
                    background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    box-shadow: 0 4px 15px -5px rgba(0, 0, 0, 0.3);
               ">
                <span class="text-white">Full Catalog</span>
                <svg class="w-4 h-4 text-red-500 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</div>