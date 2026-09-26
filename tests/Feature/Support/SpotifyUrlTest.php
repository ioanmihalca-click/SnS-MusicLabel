<?php

use App\Support\Spotify\SpotifyUrl;

it('parses the embed codes pasted on the live site', function (string $embedCode, string $type, string $id) {
    $spotifyUrl = SpotifyUrl::parse($embedCode);

    expect($spotifyUrl->type)->toBe($type)
        ->and($spotifyUrl->id)->toBe($id);
})->with([
    'track, generator code with data-testid and si' => [
        '<iframe data-testid="embed-iframe" style="border-radius:12px" src="https://open.spotify.com/embed/track/4ZyLmBV36fgNyo3IAM8xc3?utm_source=generator&si=4e40ec9a222f4395" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>',
        'track', '4ZyLmBV36fgNyo3IAM8xc3',
    ],
    'album, generator code with data-testid' => [
        '<iframe data-testid="embed-iframe" style="border-radius:12px" src="https://open.spotify.com/embed/album/7kRBMHJQEsklIRTNH0qRfp?utm_source=generator" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>',
        'album', '7kRBMHJQEsklIRTNH0qRfp',
    ],
    'album, older generator code' => [
        '<iframe style="border-radius:12px" src="https://open.spotify.com/embed/album/3zifCl5R2DaZGEmrPNUM1N?utm_source=generator" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>',
        'album', '3zifCl5R2DaZGEmrPNUM1N',
    ],
    'track, older generator code' => [
        '<iframe style="border-radius:12px" src="https://open.spotify.com/embed/track/6zxJmiH2nAmqzY377URnJX?utm_source=generator" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>',
        'track', '6zxJmiH2nAmqzY377URnJX',
    ],
    'playlist' => [
        '<iframe style="border-radius:12px" src="https://open.spotify.com/embed/playlist/28I7hCUFTyqblhgu5yGkOO?utm_source=generator" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>',
        'playlist', '28I7hCUFTyqblhgu5yGkOO',
    ],
    'track, rich-editor markup from a blog post' => [
        '<p><iframe style="border-radius: 12px;" src="https://open.spotify.com/embed/track/2B0xsnWUjm7cPLs9gGoepp?utm_source=generator" width="100%" height="352" frameborder="0" allowfullscreen="allowfullscreen" loading="lazy"></iframe></p>',
        'track', '2B0xsnWUjm7cPLs9gGoepp',
    ],
]);

it('parses share links and URIs', function (string $value, string $type, string $id) {
    $spotifyUrl = SpotifyUrl::parse($value);

    expect($spotifyUrl->type)->toBe($type)
        ->and($spotifyUrl->id)->toBe($id);
})->with([
    'plain link' => ['https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N', 'album', '3zifCl5R2DaZGEmrPNUM1N'],
    'link with si' => ['https://open.spotify.com/track/4cOdK2wGLETKBW3PvgPWqT?si=abc', 'track', '4cOdK2wGLETKBW3PvgPWqT'],
    'localised link' => ['https://open.spotify.com/intl-de/track/5LoRtT4HMphu4n2OyJn4Cr?si=bce74a18c01f40b5', 'track', '5LoRtT4HMphu4n2OyJn4Cr'],
    'embed src' => ['https://open.spotify.com/embed/playlist/28I7hCUFTyqblhgu5yGkOO?utm_source=generator', 'playlist', '28I7hCUFTyqblhgu5yGkOO'],
    'artist link' => ['https://open.spotify.com/artist/6wIX9hW2uQAVv190xXV9mA?si=WaUucV3URJaHkzoF1ct57w', 'artist', '6wIX9hW2uQAVv190xXV9mA'],
    'without scheme, with spaces' => ['  open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N/  ', 'album', '3zifCl5R2DaZGEmrPNUM1N'],
    'spotify URI' => ['spotify:track:5LoRtT4HMphu4n2OyJn4Cr', 'track', '5LoRtT4HMphu4n2OyJn4Cr'],
]);

it('rejects values that are not open.spotify.com links', function (?string $value) {
    expect(SpotifyUrl::parse($value))->toBeNull();
})->with([
    'short link' => ['https://spotify.link/aBcD3fGh1j'],
    'another host' => ['https://soundcloud.com/snow-n-stuff/the-change'],
    'lookalike host' => ['https://open.spotify.com.example.com/track/5LoRtT4HMphu4n2OyJn4Cr'],
    'unsupported type' => ['https://open.spotify.com/user/5LoRtT4HMphu4n2OyJn4Cr'],
    'truncated ID' => ['https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4C'],
    'iframe from another site' => ['<iframe style="max-width: 600px;" src="https://embed.beatport.com/?id=20186693&amp;type=track" width="100%" height="162"></iframe>'],
    'empty' => [''],
    'null' => [null],
]);

it('builds the canonical URL, URI and embed source', function () {
    $spotifyUrl = SpotifyUrl::parse('https://open.spotify.com/intl-de/track/4cOdK2wGLETKBW3PvgPWqT?si=abc');

    expect($spotifyUrl->url())->toBe('https://open.spotify.com/track/4cOdK2wGLETKBW3PvgPWqT')
        ->and($spotifyUrl->uri())->toBe('spotify:track:4cOdK2wGLETKBW3PvgPWqT')
        ->and($spotifyUrl->embedSrc())->toBe('https://open.spotify.com/embed/track/4cOdK2wGLETKBW3PvgPWqT');
});

it('builds a reference from a type and an ID', function () {
    expect(SpotifyUrl::fromParts('playlist', '28I7hCUFTyqblhgu5yGkOO')->url())
        ->toBe('https://open.spotify.com/playlist/28I7hCUFTyqblhgu5yGkOO');
});

it('refuses to build a reference from an invalid type or ID', function (string $type, string $id) {
    SpotifyUrl::fromParts($type, $id);
})->with([
    'unknown type' => ['user', '28I7hCUFTyqblhgu5yGkOO'],
    'short ID' => ['playlist', '28I7hCUFTyqblhgu'],
])->throws(InvalidArgumentException::class);

it('recognises short share links', function (string $value, bool $isShortLink) {
    expect(SpotifyUrl::isShortLink($value))->toBe($isShortLink);
})->with([
    'spotify.link' => ['https://spotify.link/aBcD3fGh1j', true],
    'spotify.app.link' => ['https://spotify.app.link/aBcD3fGh1j', true],
    'open.spotify.com' => ['https://open.spotify.com/track/5LoRtT4HMphu4n2OyJn4Cr', false],
]);

it('plays albums, tracks and playlists in the footer player, nothing else', function (string $value, bool $isPlayable) {
    expect(SpotifyUrl::parse($value)->isPlayable())->toBe($isPlayable);
})->with([
    'album' => ['https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N', true],
    'track' => ['https://open.spotify.com/track/4cOdK2wGLETKBW3PvgPWqT', true],
    'playlist' => ['spotify:playlist:28I7hCUFTyqblhgu5yGkOO', true],
    'artist' => ['https://open.spotify.com/artist/6wIX9hW2uQAVv190xXV9mA', false],
    'episode' => ['https://open.spotify.com/episode/4rOoJ6Egrf8K2IrywzwOMk', false],
    'show' => ['https://open.spotify.com/show/2MAi0BvDc6GTFvKFPXnkCL', false],
]);
