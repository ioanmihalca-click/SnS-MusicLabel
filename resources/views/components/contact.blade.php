@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
    $socials = [
        ['key' => 'twitter', 'href' => 'https://x.com/G_n_S_', 'label' => 'Twitter', 'aria' => 'Follow us on Twitter'],
        ['key' => 'facebook', 'href' => 'https://www.facebook.com/SnowNStuff', 'label' => 'Facebook', 'aria' => 'Follow us on Facebook'],
        ['key' => 'instagram', 'href' => 'https://www.instagram.com/snow_n_stuff', 'label' => 'Instagram', 'aria' => 'Follow us on Instagram'],
        ['key' => 'linkedin', 'href' => 'https://www.linkedin.com/in/glenn-forrestgate-457228a9', 'label' => 'LinkedIn', 'aria' => 'Connect on LinkedIn'],
    ];
@endphp

<section id="contact" class="py-24 bg-black" data-reveal>
    <div class="container px-4 mx-auto">
        <x-section-header eyebrow="Contact" title="Contact Us" />

        <div class="mx-auto max-w-7xl">
            <div class="grid gap-12 lg:grid-cols-3">
                <!-- Contact Info -->
                <div class="space-y-8 lg:col-span-1">
                    <!-- Location -->
                    <div class="p-6 transition-colors duration-300 group bg-gray-900/50 rounded-xl hover:bg-gray-900/70">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 transition-colors duration-300 rounded-lg bg-red-800/10 group-hover:bg-red-800/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="mb-2 text-xl font-semibold text-white">Location</h3>
                                <p class="text-gray-400">Stockholm & Romania</p>
                            </div>
                        </div>
                    </div>

                    <!-- Email Sections -->
                    <div class="p-6 transition-colors duration-300 bg-gray-900/50 rounded-xl hover:bg-gray-900/70">
                        <div class="flex items-start space-x-4">
                            <div class="p-3 rounded-lg bg-red-800/10">
                                <x-icons.envelope class="w-6 h-6 text-red-800" />
                            </div>
                            <div class="space-y-4">
                                <h3 class="mb-4 text-xl font-semibold text-white">Email</h3>

                                <div class="space-y-3">
                                    <div>
                                        <p class="font-medium text-gray-300">Bookings, Remix and Sync Requests:</p>
                                        <a href="mailto:glenn@1namm.com" class="text-red-800 transition-colors duration-300 rounded hover:text-red-700 {{ $focusRing }}">glenn@1namm.com</a>
                                    </div>

                                    <div>
                                        <p class="font-medium text-gray-300">Licensing/Booking:</p>
                                        <a href="mailto:info@1namm.com" class="text-red-800 transition-colors duration-300 rounded hover:text-red-700 {{ $focusRing }}">info@1namm.com</a>
                                    </div>

                                    <div>
                                        <p class="font-medium text-gray-300">Demo:</p>
                                        <a href="mailto:demo@1namm.com" class="text-red-800 transition-colors duration-300 rounded hover:text-red-700 {{ $focusRing }}">demo@1namm.com</a>
                                    </div>

                                    <div>
                                        <p class="font-medium text-gray-300">Web Development:</p>
                                        <a href="mailto:contact@clickstudios-digital.com" class="text-red-800 transition-colors duration-300 rounded hover:text-red-700 {{ $focusRing }}">contact@clickstudios-digital.com</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="lg:col-span-2">
                    <div class="p-8 bg-gray-900/50 rounded-xl">
                        <h3 class="mb-8 text-2xl font-bold text-white">Connect With Us</h3>

                        <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                            @foreach ($socials as $social)
                                <a href="{{ $social['href'] }}"
                                    target="_blank"
                                    rel="noreferrer"
                                    aria-label="{{ $social['aria'] }}"
                                    class="flex flex-col items-center p-4 transition-colors duration-300 group bg-gray-800/50 rounded-xl hover:bg-red-800/10 {{ $focusRing }}">
                                    <x-dynamic-component :component="'icons.social.' . $social['key']" class="w-8 h-8 mb-2 text-red-800" />
                                    <span class="text-gray-400 transition-colors duration-300 group-hover:text-red-800">{{ $social['label'] }}</span>
                                </a>
                            @endforeach
                        </div>

                        <!-- Additional Social Info -->
                        <div class="p-6 mt-8 rounded-lg bg-gray-800/30">
                            <p class="text-center text-gray-400">
                                Follow us on social media to stay updated with our latest releases, events, and artist news.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
