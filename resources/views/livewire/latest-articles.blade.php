<div class="latest-articles">
    @if($latestArticles->isNotEmpty())
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($latestArticles as $article)
                <div class="overflow-hidden bg-black bg-opacity-50 rounded-lg">
                    @if($article->cover_image)
                        <img src="{{ asset('storage/' . $article->cover_image) }}" alt="{{ $article->title }}" class="object-cover w-full h-32">
                    @else
                        <div class="w-full h-32 bg-gray-700"></div>
                    @endif
                    <div class="p-3">
                        <h4 class="mb-1 text-lg font-semibold text-red-800">
                            <a href="{{ route('blog.show', $article->slug) }}" class="transition duration-300 line-clamp-2 hover:text-red-600">
                                {{ $article->title }}
                            </a>
                        </h4>
                        <p class="mb-2 text-sm text-gray-300">
                            {{ $article->published_at->format('M j, Y') }}
                        </p>
                        <a href="{{ route('blog.show', $article->slug) }}" class="text-sm text-red-800 transition duration-300 hover:text-red-600">
                            Read more
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-center text-gray-400">No articles available at the moment.</p>
    @endif
</div>