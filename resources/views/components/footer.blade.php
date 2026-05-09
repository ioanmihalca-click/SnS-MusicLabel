@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
    $footerSocials = [
        ['key' => 'twitter', 'href' => 'https://x.com/G_n_S_', 'aria' => 'Follow us on Twitter'],
        ['key' => 'facebook', 'href' => 'https://www.facebook.com/SnowNStuff', 'aria' => 'Follow us on Facebook'],
        ['key' => 'instagram', 'href' => 'https://www.instagram.com/snow_n_stuff', 'aria' => 'Follow us on Instagram'],
        ['key' => 'linkedin', 'href' => 'https://www.linkedin.com/in/glenn-forrestgate-457228a9', 'aria' => 'Connect on LinkedIn'],
    ];
    $quickLinks = [
        ['href' => '/#artists', 'label' => 'Our Artists'],
        ['href' => '/#releases', 'label' => 'Latest Releases'],
        ['href' => '/#playlists', 'label' => 'Playlists'],
        ['href' => '/blog', 'label' => 'Blog'],
        ['href' => '/#contact', 'label' => 'Contact'],
    ];
@endphp

<footer class="bg-black border-t border-gray-900">
    <div class="container px-4 py-12 mx-auto">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Company Info -->
            <div class="space-y-6">
                <div>
                    <h3 class="text-2xl font-bold text-white">Snow n Stuff</h3>
                    <p class="mt-2 text-sm text-gray-400">
                        Electronic Music Management, Label and Production
                    </p>
                </div>

                <!-- Social Links -->
                <div class="flex space-x-4">
                    @foreach ($footerSocials as $social)
                        <a href="{{ $social['href'] }}"
                            target="_blank"
                            rel="noreferrer"
                            class="p-2 text-gray-400 transition-colors duration-300 rounded-md hover:text-red-800 {{ $focusRing }}"
                            aria-label="{{ $social['aria'] }}">
                            <x-dynamic-component :component="'icons.social.' . $social['key']" class="w-5 h-5" />
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Quick Links -->
            <div class="lg:mx-auto">
                <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase">Quick Links</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($quickLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}" class="text-base text-gray-300 transition-colors duration-300 rounded hover:text-red-800 {{ $focusRing }}">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Credits -->
            <div class="space-y-6">
                <div>
                    <h3 class="text-sm font-semibold tracking-wider text-gray-400 uppercase">Development</h3>
                    <div class="mt-4">
                        <a href="https://clickstudios-digital.com" target="_blank" rel="noreferrer" class="text-gray-300 transition-colors duration-300 rounded hover:text-red-800 {{ $focusRing }}">
                            Web application by Click Studios Digital
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="pt-8 mt-12 border-t border-gray-800">
            <p class="text-base text-center text-gray-400">
                &copy; {{ date('Y') }} Snow n Stuff. All rights reserved.
            </p>
        </div>
    </div>
</footer>
