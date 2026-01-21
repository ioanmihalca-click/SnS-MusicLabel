<section id="contact" class="relative py-32 overflow-hidden">
    <!-- Map Background -->
    <div class="absolute inset-0 opacity-15 pointer-events-none mix-blend-color-dodge">
        <img src="https://upload.wikimedia.org/wikipedia/commons/e/ec/World_map_blank_without_borders.svg" class="w-full h-full object-cover filter brightness-50 contrast-125" alt="World Map">
        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black"></div>
    </div>

    <!-- Background Gradients -->
    <div class="absolute inset-0 bg-black">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 30%, rgba(127, 29, 29, 0.08) 0%, transparent 50%);"></div>
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 70% 70%, rgba(220, 38, 38, 0.05) 0%, transparent 50%);"></div>
    </div>

    <div class="container relative px-4 mx-auto z-10">
        <!-- Section Header -->
        <div class="mb-24 text-center">
            <span class="badge-primary mb-4">
                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                </svg>
                Get In Touch
            </span>
            <h2 class="text-5xl md:text-7xl font-bold font-display text-white tracking-tighter">
                CONTACT
            </h2>
        </div>

        <div class="mx-auto max-w-7xl">
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Contact Info Column -->
                <div class="space-y-6 lg:col-span-1">
                    <!-- Location Card -->
                    <div class="group relative rounded-2xl overflow-hidden transition-all duration-500 hover:-translate-y-1"
                         style="
                            background: linear-gradient(145deg, rgba(20, 20, 20, 0.95), rgba(10, 10, 10, 0.98));
                            border: 1px solid rgba(75, 75, 75, 0.15);
                            box-shadow: 
                                0 4px 20px -5px rgba(0, 0, 0, 0.4),
                                0 2px 8px -2px rgba(0, 0, 0, 0.2),
                                inset 0 1px 0 0 rgba(255, 255, 255, 0.02);
                         ">
                        <!-- Hover gradient overlay -->
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                             style="background: radial-gradient(ellipse at 50% 0%, rgba(220, 38, 38, 0.08) 0%, transparent 70%);"></div>
                        
                        <div class="relative z-10 p-8 flex items-start space-x-6">
                            <div class="p-4 rounded-xl transition-all duration-300 group-hover:scale-110"
                                 style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.15) 0%, rgba(127, 29, 29, 0.1) 100%); border: 1px solid rgba(220, 38, 38, 0.2);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white font-display mb-3">Global HQ</h3>
                                <p class="text-gray-400 font-light">Stockholm, Sweden</p>
                                <p class="text-gray-400 font-light">Bucharest, Romania</p>
                            </div>
                        </div>
                    </div>

                    <!-- Email Card -->
                    <div class="group relative rounded-2xl overflow-hidden transition-all duration-500 hover:-translate-y-1"
                         style="
                            background: linear-gradient(145deg, rgba(20, 20, 20, 0.95), rgba(10, 10, 10, 0.98));
                            border: 1px solid rgba(75, 75, 75, 0.15);
                            box-shadow: 
                                0 4px 20px -5px rgba(0, 0, 0, 0.4),
                                0 2px 8px -2px rgba(0, 0, 0, 0.2),
                                inset 0 1px 0 0 rgba(255, 255, 255, 0.02);
                         ">
                        <!-- Hover gradient overlay -->
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                             style="background: radial-gradient(ellipse at 50% 0%, rgba(220, 38, 38, 0.08) 0%, transparent 70%);"></div>
                        
                        <div class="relative z-10 p-8">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="p-3 rounded-xl"
                                     style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.15) 0%, rgba(127, 29, 29, 0.1) 100%); border: 1px solid rgba(220, 38, 38, 0.2);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-white font-display">Direct Channels</h3>
                            </div>
                            
                            <div class="space-y-1">
                                @foreach([
                                    ['label' => 'Management', 'email' => 'glenn@1namm.com'],
                                    ['label' => 'Bookings', 'email' => 'info@1namm.com'],
                                    ['label' => 'Demos', 'email' => 'demo@1namm.com'],
                                    ['label' => 'Tech', 'email' => 'contact@clickstudios-digital.com'],
                                ] as $contact)
                                    <div class="flex justify-between items-center py-3 group/item transition-all duration-300 hover:pl-2"
                                         style="border-bottom: 1px solid rgba(255, 255, 255, 0.04);">
                                        <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ $contact['label'] }}</span>
                                        <a href="mailto:{{ $contact['email'] }}" class="text-sm text-gray-400 group-hover/item:text-red-500 transition-colors duration-300">{{ $contact['email'] }}</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media Column -->
                <div class="lg:col-span-2">
                    <div class="h-full relative rounded-2xl overflow-hidden"
                         style="
                            background: linear-gradient(145deg, rgba(20, 20, 20, 0.95), rgba(10, 10, 10, 0.98));
                            border: 1px solid rgba(75, 75, 75, 0.15);
                            box-shadow: 
                                0 4px 20px -5px rgba(0, 0, 0, 0.4),
                                0 2px 8px -2px rgba(0, 0, 0, 0.2),
                                inset 0 1px 0 0 rgba(255, 255, 255, 0.02);
                         ">
                        <!-- Decorative Big Icon -->
                        <div class="absolute -right-16 -bottom-16 text-white/[0.02] select-none pointer-events-none">
                             <svg class="w-80 h-80" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/></svg>
                        </div>
              
                        <div class="relative z-10 p-8 md:p-12">
                            <h3 class="text-3xl font-bold text-white font-display mb-3">Connect With Us</h3>
                            <p class="text-gray-400 mb-12 max-w-md leading-relaxed">Join our community across platforms. Stay updated with the latest releases, events, and artist announcements.</p>
                            
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                                @foreach([
                                    ['name' => 'Twitter', 'url' => 'https://x.com/G_n_S_', 'icon' => 'M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z'],
                                    ['name' => 'Facebook', 'url' => 'https://www.facebook.com/SnowNStuff', 'icon' => 'M22.675 0H1.325C.593 0 0 .593 0 1.325v21.351C0 23.407.593 24 1.325 24H12.82v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116c.73 0 1.323-.593 1.323-1.325V1.325C24 .593 23.407 0 22.675 0z'],
                                    ['name' => 'Instagram', 'url' => 'https://www.instagram.com/snow_n_stuff', 'icon' => 'M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.897 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.897-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z'],
                                    ['name' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/glenn-forrestgate-457228a9', 'icon' => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.203 0 22.225 0z'],
                                ] as $social)
                                    <a href="{{ $social['url'] }}" 
                                       target="_blank" 
                                       rel="noreferrer"
                                       class="group/icon flex flex-col items-center justify-center p-6 rounded-2xl aspect-square transition-all duration-300 hover:scale-105 hover:-translate-y-1"
                                       style="
                                            background: linear-gradient(145deg, rgba(30, 30, 30, 0.8), rgba(20, 20, 20, 0.9));
                                            border: 1px solid rgba(75, 75, 75, 0.12);
                                       "
                                    >
                                        <svg class="w-8 h-8 mb-3 text-gray-500 group-hover/icon:text-red-500 transition-colors duration-300" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="{{ $social['icon'] }}"/>
                                        </svg>
                                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest group-hover/icon:text-white transition-colors duration-300">{{ $social['name'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>