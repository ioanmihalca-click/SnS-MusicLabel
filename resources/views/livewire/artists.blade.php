<div id="artists" class="relative py-32 bg-black overflow-hidden">
    <!-- Ambient Background -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 left-0 w-[600px] h-[600px] bg-red-900/15 rounded-full blur-[150px] -translate-x-1/2 animate-pulse-soft"></div>
        <div class="absolute bottom-1/4 right-0 w-[600px] h-[600px] bg-red-600/8 rounded-full blur-[150px] translate-x-1/2 animate-pulse-soft" style="animation-delay: 1.5s;"></div>
    </div>

    <div class="container relative px-4 mx-auto z-10">
        <!-- Section Header -->
        <div class="mb-24 text-center">
            <span class="badge-primary mb-4">
                <span class="flex w-2 h-2 bg-red-500 rounded-full animate-pulse mr-2"></span>
                The Roster
            </span>
            <h2 class="text-5xl md:text-7xl font-bold font-display text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-200 to-gray-500 tracking-tighter">
                OUR ARTISTS
            </h2>
        </div>

        <!-- Artists Grid -->
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($artists as $artist)
                <div 
                    x-data="{ 
                        hover: false,
                        expanded: false,
                        x: 0,
                        y: 0,
                        rotateX: 0,
                        rotateY: 0,
                        handleMouseMove(e) {
                            const rect = $el.getBoundingClientRect();
                            const width = rect.width;
                            const height = rect.height;
                            const mouseX = e.clientX - rect.left;
                            const mouseY = e.clientY - rect.top;
                            
                            const xPct = mouseX / width - 0.5;
                            const yPct = mouseY / height - 0.5;
                            
                            this.x = xPct * 20;
                            this.y = yPct * 20;
                            this.rotateY = xPct * 15;
                            this.rotateX = yPct * -15;
                        },
                        reset() {
                            this.x = 0;
                            this.y = 0;
                            this.rotateX = 0;
                            this.rotateY = 0;
                            this.hover = false;
                        }
                    }"
                    @mousemove="handleMouseMove($event); hover = true"
                    @mouseleave="reset()"
                    class="relative perspective-1000 group"
                    style="perspective: 1000px;"
                >
                    <div
                        class="relative min-h-[400px] flex flex-col justify-between p-8 rounded-2xl transition-all duration-300 ease-out"
                        style="
                            background: linear-gradient(145deg, rgba(25, 25, 25, 0.95), rgba(10, 10, 10, 0.98));
                            border: 1px solid rgba(75, 75, 75, 0.2);
                            box-shadow: 
                                0 4px 20px -5px rgba(0, 0, 0, 0.5),
                                0 2px 8px -2px rgba(0, 0, 0, 0.3),
                                inset 0 1px 0 0 rgba(255, 255, 255, 0.03);
                        "
                        :style="`
                            transform: rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(${hover ? 1.02 : 1}); 
                            transform-style: preserve-3d;
                            ${hover ? 'border-color: rgba(220, 38, 38, 0.25); box-shadow: 0 15px 40px -10px rgba(0, 0, 0, 0.6), 0 8px 20px -5px rgba(0, 0, 0, 0.4), 0 0 40px -15px rgba(220, 38, 38, 0.2), inset 0 1px 0 0 rgba(255, 255, 255, 0.05);' : ''}
                        `"
                    >
                        <!-- Subtle Gradient Overlay -->
                        <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none" 
                             style="background: radial-gradient(ellipse at 50% 0%, rgba(220, 38, 38, 0.08) 0%, transparent 60%);"></div>

                        <!-- Lighting Effect -->
                        <div 
                            class="absolute inset-0 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"
                            style="background: radial-gradient(circle at center, rgba(220, 38, 38, 0.1) 0%, transparent 70%); mix-blend-mode: screen;"
                            :style="`transform: translate(${x}px, ${y}px)`"
                        ></div>

                        <!-- Massive Background Number (Parallax) -->
                        <div 
                            class="absolute top-0 right-4 text-[12rem] font-bold text-white/[0.03] font-display leading-none select-none pointer-events-none transition-transform duration-200"
                            :style="`transform: translateZ(20px) translate(${x * -0.5}px, ${y * -0.5}px)`"
                        >
                            {{ str_pad($artist->order, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <!-- Content Top -->
                        <div class="relative z-10" style="transform: translateZ(30px);">
                            <h4 class="text-4xl font-bold text-white font-display mb-2 group-hover:text-red-500 transition-colors duration-300">
                                {{ $artist->name }}
                            </h4>
                            <div class="h-1 w-12 rounded-full mb-6 group-hover:w-24 transition-all duration-500" 
                                 style="background: linear-gradient(90deg, #dc2626 0%, #991b1b 100%);"></div>
                        </div>

                        <!-- Content Middle (Description) -->
                        <div class="relative z-10 flex-grow" style="transform: translateZ(20px);">
                            <div class="text-gray-400 font-light leading-relaxed">
                                <div x-ref="fullDescription" class="hidden">{!! $artist->description !!}</div>
                                <p 
                                    x-html="expanded ? $refs.fullDescription.innerHTML : ($refs.fullDescription.textContent.length > 150 ? $refs.fullDescription.textContent.substring(0, 150) + '...' : $refs.fullDescription.innerHTML)"
                                    class="text-sm tracking-wide"
                                ></p>
                                <button 
                                    @click.stop="expanded = !expanded"
                                    x-show="$refs.fullDescription.textContent.length > 150"
                                    class="mt-4 text-xs font-bold text-red-500 uppercase tracking-widest hover:text-white transition-colors duration-300 flex items-center gap-2"
                                >
                                    <span x-text="expanded ? 'READ LESS' : 'READ BIO'"></span>
                                    <svg x-show="!expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    <svg x-show="expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content Bottom (Spotify) -->
                        <div class="relative z-10 mt-8 pt-6" style="transform: translateZ(30px); border-top: 1px solid rgba(255, 255, 255, 0.05);">
                            <a href="{{ $artist->spotify_url }}" 
                               target="_blank" 
                               rel="noreferrer"
                               class="flex items-center justify-between group/link"
                            >
                                <span class="text-sm font-medium text-gray-400 group-hover/link:text-white transition-colors duration-300">Listen on Spotify</span>
                                <div class="p-2.5 rounded-xl transition-all duration-300 transform group-hover/link:scale-110"
                                     style="background: linear-gradient(135deg, rgba(29, 185, 84, 0.15) 0%, rgba(29, 185, 84, 0.05) 100%); border: 1px solid rgba(29, 185, 84, 0.2);">
                                    <svg class="w-5 h-5 text-[#1DB954] group-hover/link:text-white transition-colors duration-300" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                                    </svg>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>