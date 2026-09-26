<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Release;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ArtistController extends Controller
{
    public const DESCRIPTION = "The artists on the Snow 'n' Stuff roster and under its management, with their bios, highlights and releases on the label.";

    /**
     * The roster.
     */
    public function index(): View
    {
        $path = route('artists.index', absolute: false);
        $trail = ['/' => 'Home', $path => 'Artists'];

        return view('artists.index', [
            'artists' => Artist::query()->inRosterOrder()->get(),
            'trail' => $trail,
            'seo' => new SeoData(
                title: 'Artists - '.SeoData::SITE_NAME,
                description: self::DESCRIPTION,
                path: $path,
                schema: [Schema::breadcrumbs($trail)],
            ),
        ]);
    }

    /**
     * An artist page: portrait, bio, highlights, discography and the rest of the
     * roster. "Play latest" loads the newest release Spotify can play.
     */
    public function show(Artist $artist): View
    {
        $artist->load(['releases' => fn (BelongsToMany $query) => $query->with('artists')->newestFirst()]);

        $path = route('artists.show', $artist->slug, absolute: false);
        $trail = [
            '/' => 'Home',
            route('artists.index', absolute: false) => 'Artists',
            $path => $artist->name,
        ];

        return view('artists.show', [
            'artist' => $artist,
            'photoUrl' => $artist->photoUrl(),
            'latestPlayableRelease' => $artist->releases->first(fn (Release $release): bool => $release->playUri() !== null),
            'trail' => $trail,
            'otherArtists' => Artist::query()->whereKeyNot($artist->getKey())->inRosterOrder()->get(),
            'seo' => new SeoData(
                title: $artist->name.' - '.SeoData::SITE_NAME,
                description: $artist->summary(),
                path: $path,
                image: $artist->photoUrl(),
                imageIsThumbnail: ! $artist->hasUploadedArtwork(),
                schema: [
                    Schema::breadcrumbs($trail),
                    Schema::musicGroup($artist),
                ],
            ),
        ]);
    }
}
