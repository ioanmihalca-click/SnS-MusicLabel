@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
    $navLinks = [
        ['href' => '/', 'label' => 'Home'],
        ['href' => '/#about', 'label' => 'About'],
        ['href' => '/#artists', 'label' => 'Artists'],
        ['href' => '/#releases', 'label' => 'Releases'],
        ['href' => '/#playlists', 'label' => 'Playlists'],
        ['href' => '/#gallery', 'label' => 'Gallery'],
        ['href' => '/blog', 'label' => 'Blog'],
        ['href' => '/#contact', 'label' => 'Contact'],
    ];
@endphp

<div class="relative">
    <!-- Top Bar - hides on scroll -->
    <div x-data="{ isVisible: true }" x-show="isVisible" @scroll.window="isVisible = window.pageYOffset < 100"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-4"
        class="fixed top-0 z-50 w-full py-2 bg-black/90 backdrop-blur-sm">
        <div class="container px-4 mx-auto">
            <div class="flex items-center justify-center md:justify-between">
                <div class="flex space-x-6 text-sm">
                    <a href="mailto:info@1namm.com"
                        class="flex items-center space-x-2 font-semibold text-gray-300 transition-colors duration-300 rounded hover:text-red-700 {{ $focusRing }}">
                        <x-icons.envelope />
                        <span>info@1namm.com</span>
                    </a>
                    <a href="mailto:glenn@1namm.com"
                        class="flex items-center space-x-2 font-semibold text-gray-300 transition-colors duration-300 rounded hover:text-red-700 {{ $focusRing }}">
                        <x-icons.envelope />
                        <span>glenn@1namm.com</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation - sticky on scroll -->
    <header x-data="{ isScrolled: false, mobileMenuOpen: false }" @scroll.window="isScrolled = window.pageYOffset > 100"
        :class="{
            'bg-black/95 shadow-lg': isScrolled,
            'bg-transparent': !isScrolled,
            'top-0': isScrolled,
            'top-10': !isScrolled
        }"
        class="fixed z-40 w-full transition-all duration-300">
        <div class="container px-4 mx-auto">
            <nav class="flex items-center justify-between py-4">
                <!-- Logo -->
                <a href="/"
                    aria-label="Snow 'n' Stuff homepage"
                    class="group inline-flex items-center gap-2.5 text-white transition-colors duration-300 rounded hover:text-red-500 {{ $focusRing }}">
                    <x-icons.logomark class="w-7 h-7 text-red-500 transition-transform duration-500 group-hover:rotate-180" />
                    <span class="font-display font-black tracking-tight uppercase text-xl leading-none">
                        <x-brand-name uppercase />
                    </span>
                </a>

                <!-- Desktop Navigation -->
                <div class="items-center hidden space-x-8 md:flex">
                    @foreach ($navLinks as $link)
                        <a href="{{ $link['href'] }}"
                            class="font-semibold text-white transition-colors duration-300 rounded hover:text-red-700 {{ $focusRing }}">{{ $link['label'] }}</a>
                    @endforeach
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    type="button"
                    aria-label="Toggle navigation menu"
                    aria-controls="mobile-menu"
                    :aria-expanded="mobileMenuOpen.toString()"
                    class="z-50 p-2 text-white transition-colors duration-300 rounded md:hidden hover:text-red-800 {{ $focusRing }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" aria-hidden="true">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </nav>

            <!-- Mobile Menu Panel - full screen -->
            <div id="mobile-menu"
                x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="fixed inset-0 z-40 min-h-screen bg-black/95 backdrop-blur-lg md:hidden"
                style="top: 0; left: 0; right: 0; bottom: 0;"
                @click.away="mobileMenuOpen = false">
                <!-- Menu Links Container -->
                <div class="flex flex-col items-center justify-center min-h-screen p-4 space-y-6">
                    @foreach ($navLinks as $link)
                        <a href="{{ $link['href'] }}" @click="mobileMenuOpen = false"
                            class="w-full max-w-sm p-4 text-xl text-center text-white transition-all duration-300 rounded-lg hover:bg-red-800/20 hover:text-red-500 {{ $focusRing }}">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </header>
</div>
