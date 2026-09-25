<iframe
    src="{{ $spotifyUrl->embedSrc() }}"
    width="100%"
    height="{{ $height }}"
    {{ $attributes->merge(['title' => 'Spotify player', 'style' => 'border-radius: 12px']) }}
    frameborder="0"
    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
    loading="lazy"
></iframe>
