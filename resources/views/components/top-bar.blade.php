<div class="relative z-50">
    <!-- Main Navigation - Floating Glass Design -->
    <header 
        x-data="{ 
            isScrolled: false, 
            mobileMenuOpen: false,
            init() {
                window.addEventListener('scroll', () => {
                    this.isScrolled = window.pageYOffset > 50;
                });
            }
        }" 
        class="fixed left-0 right-0 z-50 transition-all duration-500 ease-out"
        :class="{
            'top-4': !isScrolled,
            'top-0': isScrolled
        }"
    >
        <div class="px-4 mx-auto max-w-5xl">
            <div 
                class="relative flex items-center justify-center px-6 py-4 transition-all duration-500"
                :class="{
                    'rounded-2xl': !isScrolled,
                    'rounded-none border-x-0 border-t-0': isScrolled
                }"
                :style="isScrolled 
                    ? 'background: linear-gradient(180deg, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.9) 100%); border-bottom: 1px solid rgba(220, 38, 38, 0.15); backdrop-filter: blur(20px); box-shadow: 0 4px 30px -10px rgba(0, 0, 0, 0.5);'
                    : 'background: linear-gradient(145deg, rgba(15, 15, 15, 0.85), rgba(10, 10, 10, 0.9)); border: 1px solid rgba(75, 75, 75, 0.15); backdrop-filter: blur(20px); box-shadow: 0 8px 32px -8px rgba(0, 0, 0, 0.5), inset 0 1px 0 0 rgba(255, 255, 255, 0.03);'"
            >
                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-1">
                    @foreach([
                        ['label' => 'Home', 'url' => '/'],
                        ['label' => 'About', 'url' => '/#about'],
                        ['label' => 'Artists', 'url' => '/#artists'],
                        ['label' => 'Releases', 'url' => '/#releases'],
                        ['label' => 'Playlists', 'url' => '/#playlists'],
                        ['label' => 'Gallery', 'url' => '/#gallery'],
                        ['label' => 'Blog', 'url' => '/blog'],
                        ['label' => 'Contact', 'url' => '/#contact'],
                    ] as $item)
                        <a href="{{ $item['url'] }}" 
                           class="relative px-4 py-2 text-sm font-medium text-gray-400 transition-all duration-300 rounded-lg group hover:text-white overflow-hidden">
                            <span class="relative z-10">{{ $item['label'] }}</span>
                            <div class="absolute inset-0 transition-opacity duration-300 opacity-0 rounded-lg group-hover:opacity-100"
                                 style="background: linear-gradient(145deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));"></div>
                            <div class="absolute bottom-0 left-0 w-full h-[2px] transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"
                                 style="background: linear-gradient(90deg, transparent, rgba(220, 38, 38, 0.7), transparent);"></div>
                        </a>
                    @endforeach
                </nav>

                <!-- Mobile Menu Button -->
                <button 
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="p-2 text-white transition-colors duration-300 md:hidden hover:text-red-500"
                >
                    <div class="w-6 h-5 relative flex flex-col justify-between">
                        <span class="w-full h-0.5 bg-current transform transition-all duration-300 origin-left" :class="{'rotate-45 translate-x-px': mobileMenuOpen}"></span>
                        <span class="w-full h-0.5 bg-current transition-all duration-300" :class="{'opacity-0': mobileMenuOpen}"></span>
                        <span class="w-full h-0.5 bg-current transform transition-all duration-300 origin-left" :class="{'-rotate-45 translate-x-px': mobileMenuOpen}"></span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div 
            x-show="mobileMenuOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="absolute top-20 left-4 right-4 z-40 md:hidden"
            @click.away="mobileMenuOpen = false"
        >
            <div class="p-5 rounded-2xl"
                 style="
                    background: linear-gradient(145deg, rgba(15, 15, 15, 0.98), rgba(10, 10, 10, 0.99));
                    border: 1px solid rgba(75, 75, 75, 0.15);
                    backdrop-filter: blur(30px);
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
                 ">
                <nav class="flex flex-col space-y-2">
                    @foreach([
                        ['label' => 'Home', 'url' => '/'],
                        ['label' => 'About', 'url' => '/#about'],
                        ['label' => 'Artists', 'url' => '/#artists'],
                        ['label' => 'Releases', 'url' => '/#releases'],
                        ['label' => 'Playlists', 'url' => '/#playlists'],
                        ['label' => 'Gallery', 'url' => '/#gallery'],
                        ['label' => 'Blog', 'url' => '/blog'],
                        ['label' => 'Contact', 'url' => '/#contact'],
                    ] as $item)
                        <a href="{{ $item['url'] }}" 
                           @click="mobileMenuOpen = false"
                           class="flex items-center justify-between px-4 py-3.5 text-lg font-medium text-gray-300 transition-all rounded-xl hover:text-white group"
                           style="background: transparent;"
                           x-on:mouseenter="$el.style.background = 'linear-gradient(145deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02))'"
                           x-on:mouseleave="$el.style.background = 'transparent'">
                            <span class="font-display">{{ $item['label'] }}</span>
                            <svg class="w-5 h-5 opacity-0 text-red-500 transform -translate-x-2 transition-all group-hover:opacity-100 group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>
</div>
