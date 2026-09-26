{{--
    Sticky site header: logo, the main navigation and, below md, a menu
    toggled with Alpine (bundled with Livewire).
--}}
@php
    $navLinks = [
        ['label' => 'Releases', 'href' => route('releases.index'), 'active' => request()->routeIs('releases.*')],
        ['label' => 'Artists', 'href' => route('artists.index'), 'active' => request()->routeIs('artists.*')],
        ['label' => 'Playlists', 'href' => route('playlists.index'), 'active' => request()->routeIs('playlists.*')],
        ['label' => 'News', 'href' => route('blog.index'), 'active' => request()->routeIs('blog.*')],
        ['label' => 'About', 'href' => route('about'), 'active' => request()->routeIs('about')],
    ];
@endphp

<header
    data-markdown-ignore
    x-data="{ menuOpen: false }"
    @keydown.escape.window="menuOpen = false"
    class="sticky top-0 z-50 border-b border-rule bg-ink/[.78] backdrop-blur-[14px]"
>
    <div class="site-wrap">
        <div class="flex min-h-16 items-center gap-6">
            <a href="{{ route('home') }}" wire:navigate aria-label="Snow 'n' Stuff, home" class="group inline-flex flex-none items-center gap-2.5 text-frost">
                <x-icons.logomark class="h-[30px] w-[30px] text-signal transition-transform duration-700 ease-[cubic-bezier(.2,.7,.1,1)] group-hover:rotate-180" />
                <span class="font-display text-[21px] font-black uppercase leading-none tracking-[.01em]">Snow 'n' Stuff</span>
            </a>

            <nav aria-label="Main" class="ml-3 hidden gap-[26px] md:flex">
                @foreach ($navLinks as $link)
                    <a
                        href="{{ $link['href'] }}"
                        wire:navigate
                        @if ($link['active']) aria-current="page" @endif
                        @class([
                            'border-b py-1.5 text-[15px] font-medium transition-colors hover:border-frost hover:text-frost',
                            'border-frost text-frost' => $link['active'],
                            'border-transparent text-mist' => ! $link['active'],
                        ])
                    >{{ $link['label'] }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2.5 md:hidden">
                <button
                    type="button"
                    @click="menuOpen = ! menuOpen"
                    aria-controls="mobile-nav"
                    :aria-expanded="menuOpen.toString()"
                    aria-expanded="false"
                    class="grid h-10 w-10 place-items-center rounded-full border border-rule2 text-frost transition-colors hover:border-frost"
                >
                    <span class="sr-only" x-text="menuOpen ? 'Close menu' : 'Open menu'">Open menu</span>
                    <svg x-show="! menuOpen" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    <svg x-show="menuOpen" x-cloak class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>
        </div>

        <nav id="mobile-nav" aria-label="Mobile" x-show="menuOpen" x-cloak class="border-t border-rule pb-[18px] pt-2.5 md:hidden">
            @foreach ($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    wire:navigate
                    @if ($link['active']) aria-current="page" @endif
                    @class([
                        'block py-1 font-display text-[30px] font-extrabold uppercase leading-tight',
                        'text-frost' => $link['active'],
                        'text-mist hover:text-frost' => ! $link['active'],
                    ])
                >{{ $link['label'] }}</a>
            @endforeach
        </nav>
    </div>
</header>
