<!-- About Section -->
<section id="about" class="relative py-32 overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-black">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 50%, rgba(127, 29, 29, 0.08) 0%, transparent 60%);"></div>
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 80% 80%, rgba(220, 38, 38, 0.05) 0%, transparent 50%);"></div>
    </div>

    <div class="container relative px-4 mx-auto">
        <!-- Section Header -->
        <div class="mb-20 text-center">
            <span class="badge-primary mb-4">
                About
            </span>
            <h2 class="text-4xl md:text-5xl font-bold font-display">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-red-500 to-red-800">Snow 'n' Stuff</span>
            </h2>
        </div>

        <div class="grid items-center gap-16 lg:grid-cols-2">
            <!-- Image Container -->
            <div 
                x-data="{ hover: false }"
                @mouseenter="hover = true"
                @mouseleave="hover = false"
                class="relative group"
            >
                <!-- Corner Decorations with gradient -->
                <div class="absolute w-20 h-20 transition-all duration-500 left-4 top-4 group-hover:left-1 group-hover:top-1"
                     style="border-top: 2px solid; border-left: 2px solid; border-image: linear-gradient(135deg, #dc2626, #991b1b) 1;"></div>
                <div class="absolute w-20 h-20 transition-all duration-500 right-4 bottom-4 group-hover:right-1 group-hover:bottom-1"
                     style="border-bottom: 2px solid; border-right: 2px solid; border-image: linear-gradient(315deg, #dc2626, #991b1b) 1;"></div>
                
                <!-- Image Card -->
                <div class="relative p-1 rounded-2xl transition-all duration-500 group-hover:-translate-y-1"
                     style="background: linear-gradient(145deg, rgba(60, 60, 60, 0.15), rgba(30, 30, 30, 0.1));">
                    <div class="overflow-hidden rounded-xl"
                         style="box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.5);">
                        <img 
                            src="/assets/img/logo-sns.webp" 
                            alt="About Snow n Stuff" 
                            class="w-full transition-transform duration-700 group-hover:scale-[1.03]"
                        >
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="space-y-8">
                <h3 class="text-3xl font-bold text-white font-display">
                    Management, Label and Music Production
                </h3>

                <p class="text-lg text-gray-400 italic leading-relaxed"
                   style="border-left: 3px solid rgba(220, 38, 38, 0.5); padding-left: 1.5rem;">
                    Snow 'n' Stuff is releasing Tech House, Deep House, House and Techno.
                    Management for: THK, G&S, Snow N Stuff and Style Da Kid among others.
                    Tastemaker & Curator of several Spotify playlists. Deep House & Ibiza and many more playlists.
                </p>

                <ul class="space-y-6">
                    @foreach([
                        'Snow N Stuff stands as a contemporary music label fully attuned to the current era. Its members possess profound proficiency spanning the entire spectrum of musical domains. With over two and a half decades dedicated to the art of Artists and Repertoire (A&R), coupled with substantial involvement in music production, mixing, mastering, and sound design, their collective experience is nothing short of remarkable.',
                        'Garnering Grammy nominations, facilitating music licensing on a global scale, securing sync placements across international networks, and amassing millions of radio airplays, Snow N Stuff has indisputably achieved a comprehensive array of accomplishments in the music industry.',
                        'Snow \'n\' Stuff is actively creating immersive live events and experiences, leveraging their deep roots in the electronic music community and their ability to secure sync placements and airplay. These events feature performances by their roster of artists, as well as collaborations with other labels, promoters, and venues.'
                    ] as $item)
                        <li class="flex items-start space-x-4 group/item">
                            <!-- Icon Container -->
                            <div class="flex-shrink-0 p-2.5 rounded-xl transition-all duration-300 group-hover/item:scale-110"
                                 style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.15) 0%, rgba(127, 29, 29, 0.1) 100%); border: 1px solid rgba(220, 38, 38, 0.2);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <!-- Text -->
                            <span class="flex-1 text-gray-400 leading-relaxed text-sm">
                                {{ $item }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>