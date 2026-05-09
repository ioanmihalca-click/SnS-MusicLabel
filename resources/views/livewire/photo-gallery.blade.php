<div id="gallery" class="py-24 bg-black" data-reveal>
    <div class="container px-4 mx-auto">
        <x-section-header eyebrow="Photos" title="Some photos of Our Artists" />

        <!-- Photo Grid (the outer section already carries id="gallery" so Fancybox binds correctly) -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            @foreach($photos as $photo)
                <div class="relative overflow-hidden aspect-square group rounded-xl">
                    <a
                        data-fancybox="gallery"
                        data-src="{{ asset('storage/' . $photo->image_path) }}"
                        aria-label="Open {{ $photo->title }} in lightbox"
                        class="block w-full h-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black"
                    >
                        <img
                            src="{{ asset('storage/' . $photo->image_path) }}"
                            alt="{{ $photo->title }}"
                            width="600"
                            height="600"
                            loading="lazy"
                            decoding="async"
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
