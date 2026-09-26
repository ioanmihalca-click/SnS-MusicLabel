<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use App\Support\Spotify\SpotifyThumbnail;
use Illuminate\Contracts\View\View;

class PlaylistsController extends Controller
{
    public const DESCRIPTION = "Spotify playlists curated by Snow 'n' Stuff, tastemaker and curator: deep house, Ibiza, melodic techno, drum & bass, rave and chill.";

    /**
     * The Spotify playlists curated by the label.
     */
    public function __invoke(SpotifyThumbnail $thumbnails): View
    {
        $path = route('playlists.index', absolute: false);
        $trail = ['/' => 'Home', $path => 'Playlists'];
        $playlists = Playlist::query()->active()->get();

        $thumbnails->warmArtwork($playlists);

        return view('playlists.index', [
            'playlists' => $playlists,
            'trail' => $trail,
            'seo' => new SeoData(
                title: 'Playlists - '.SeoData::SITE_NAME,
                description: self::DESCRIPTION,
                path: $path,
                schema: [
                    Schema::breadcrumbs($trail),
                    ...$playlists->map(fn (Playlist $playlist): array => Schema::musicPlaylist($playlist)),
                ],
            ),
        ]);
    }
}
