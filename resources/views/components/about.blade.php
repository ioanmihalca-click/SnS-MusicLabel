<!-- About Section -->
<section id="about" class="relative py-24">
    <!-- Background with Overlay -->
    <div class="absolute inset-0 bg-black">
        <div class="absolute inset-0 bg-gradient-to-b from-black/90 to-black/70"></div>
    </div>

    <div class="container relative px-4 mx-auto">
        <div class="mb-16 text-center">
            <h2 class="mb-3 text-sm tracking-wider text-gray-400 uppercase">About</h2>
            <p class="text-4xl font-bold text-red-800">Snow n Stuff</p>
        </div>

        <div class="grid items-center gap-12 lg:grid-cols-2">
            <!-- Image Container -->
            <div 
                x-data="{ hover: false }"
                @mouseenter="hover = true"
                @mouseleave="hover = false"
                class="relative group"
            >
                <!-- Decorative Corners -->
                <div class="absolute w-16 h-16 transition-all duration-300 border-t-2 border-l-2 border-red-800 left-5 top-5 group-hover:left-2 group-hover:top-2"></div>
                <div class="absolute w-16 h-16 transition-all duration-300 border-b-2 border-r-2 border-red-800 right-5 bottom-5 group-hover:right-2 group-hover:bottom-2"></div>
                
                <!-- Main Image -->
                <img 
                    src="/assets/img/logo-sns.webp" 
                    alt="About Snow n Stuff" 
                    class="w-full rounded-lg shadow-2xl transition-transform duration-300 group-hover:scale-[1.02]"
                >
            </div>

            <!-- Content -->
            <div class="space-y-6 text-gray-300">
                <h3 class="mb-6 text-2xl font-bold text-white">
                    Management, Label and Music Production
                </h3>

                <p class="italic">
                    Snow `n` Stuff is releasing Tech House, Deep House, House and Techno.
                    Management for: THK, G&S, Snow N Stuff and Style Da Kid among others.
                    Tastemaker & Curator of several Spotify playlists. Deep House & Ibiza and many more playlists.
                </p>

                <ul class="space-y-4">
                    <li class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mt-1 text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="flex-1">
                            Snow N Stuff stands as a contemporary music label fully attuned to the current era. Its members possess profound proficiency spanning the entire spectrum of musical domains. With over two and a half decades dedicated to the art of Artists and Repertoire (A&R), coupled with substantial involvement in music production, mixing, mastering, and sound design, their collective experience is nothing short of remarkable.
                        </span>
                    </li>

                    <li class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mt-1 text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="flex-1">
                            Garnering Grammy nominations, facilitating music licensing on a global scale, securing sync placements across international networks, and amassing millions of radio airplays, Snow N Stuff has indisputably achieved a comprehensive array of accomplishments in the music industry.
                        </span>
                    </li>

                    <li class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mt-1 text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="flex-1">
                            Snow 'n' Stuff is actively creating immersive live events and experiences, leveraging their deep roots in the electronic music community and their ability to secure sync placements and airplay. These events feature performances by their roster of artists, as well as collaborations with other labels, promoters, and venues.
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>