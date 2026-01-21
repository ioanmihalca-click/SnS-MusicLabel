<div id="gallery" class="py-24 md:py-32 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-black">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 20% 30%, rgba(127, 29, 29, 0.06) 0%, transparent 50%);"></div>
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 80% 70%, rgba(220, 38, 38, 0.04) 0%, transparent 50%);"></div>
    </div>

    <div class="container relative px-4 mx-auto z-10">
        <!-- Section Header -->
        <div class="mb-16 text-center">
            <span class="badge-primary mb-4">
                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                </svg>
                Photos
            </span>
            <h2 class="text-4xl md:text-5xl font-bold font-display">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-red-500 to-red-800">Our Artists Gallery</span>
            </h2>
        </div>

        <!-- Photo Grid -->
        <div class="grid grid-cols-2 gap-4 md:gap-6 md:grid-cols-3 lg:grid-cols-4">
            @foreach($photos as $photo)
                <div class="relative aspect-square group">
                    <!-- Card Container -->
                    <div class="relative w-full h-full rounded-2xl overflow-hidden transition-all duration-500 group-hover:-translate-y-2 group-hover:scale-[1.02]"
                         style="
                            background: linear-gradient(145deg, rgba(25, 25, 25, 0.9), rgba(10, 10, 10, 0.95));
                            border: 1px solid rgba(75, 75, 75, 0.15);
                            box-shadow: 
                                0 4px 20px -5px rgba(0, 0, 0, 0.4),
                                0 2px 8px -2px rgba(0, 0, 0, 0.2);
                         ">
                        
                        <a 
                            data-fancybox="gallery"
                            data-src="{{ asset('storage/' . $photo->image_path) }}"
                            class="block w-full h-full"
                        >
                            <img 
                                src="{{ asset('storage/' . $photo->image_path) }}" 
                                alt="{{ $photo->title }}"
                                loading="lazy"
                                class="object-cover w-full h-full transition-transform duration-700 group-hover:scale-110"
                            >
                            
                            <!-- Overlay -->
                            <div class="absolute inset-0 transition-opacity duration-500 opacity-0 group-hover:opacity-100"
                                 style="background: linear-gradient(180deg, transparent 0%, transparent 40%, rgba(0, 0, 0, 0.9) 100%);">
                                
                                <!-- Content -->
                                <div class="absolute bottom-0 left-0 right-0 p-5 translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                                    <h3 class="font-bold text-white truncate text-lg">{{ $photo->title }}</h3>
                                    
                                    <!-- View Icon -->
                                    <div class="flex items-center gap-2 mt-2 text-sm text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>View</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Hover Border Glow -->
                            <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                                 style="box-shadow: inset 0 0 0 1px rgba(220, 38, 38, 0.3);"></div>
                        </a>
                    </div>

                    <!-- Shadow Glow -->
                    <div class="absolute -inset-2 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-700 pointer-events-none -z-10"
                         style="background: radial-gradient(ellipse at center, rgba(220, 38, 38, 0.12) 0%, transparent 70%);"></div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-16">
            {{ $photos->links('vendor.livewire.tailwind') }}
        </div>
    </div>
</div>