<div class="py-24 bg-black">
    <div class="container px-4 mx-auto">
        <!-- Section Header -->
        <div class="mb-16 text-center">
            <h2 class="mb-3 text-sm tracking-wider text-gray-400 uppercase">Photos</h2>
            <p class="text-4xl font-bold text-red-800">Some photos of Our Artists</p>
        </div>

        <!-- Photo Grid -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4" id="gallery">
            @foreach($photos as $photo)
                <div class="relative overflow-hidden aspect-square group rounded-xl">
                    <a 
                        data-fancybox="gallery"
                        data-src="{{ asset('storage/' . $photo->image_path) }}"
                        class="block w-full h-full"
                    >
                        <img 
                            src="{{ asset('storage/' . $photo->image_path) }}" 
                            alt="{{ $photo->title }}"
                            loading="lazy"
                            class="object-cover w-full h-full transition-transform duration-500 transform group-hover:scale-110"
                        >
                        
                        <!-- Overlay -->
                        <div class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent group-hover:opacity-100">
                            <div class="absolute bottom-0 left-0 right-0 p-4">
                                <h3 class="font-semibold text-white truncate">{{ $photo->title }}</h3>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            {{ $photos->links('vendor.livewire.tailwind') }}
        </div>
    </div>
</div>