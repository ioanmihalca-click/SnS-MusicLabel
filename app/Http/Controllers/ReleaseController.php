<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReleaseController extends Controller
{
    private const MORE_RELEASES_LIMIT = 4;

    /**
     * A release page: artwork, credit, listen links, tracklist, story, support
     * and more releases by the same artists.
     */
    public function show(Release $release): View
    {
        $release->load(['artists', 'tracks']);

        $path = route('releases.show', $release->slug, absolute: false);
        $trail = [
            '/' => 'Home',
            route('releases.index', absolute: false) => 'Releases',
            $path => $release->title,
        ];

        return view('releases.show', [
            'release' => $release,
            'coverUrl' => $release->coverUrl(),
            'trail' => $trail,
            'mainArtist' => $release->artists->first(),
            'moreReleases' => $this->moreReleasesLike($release),
            'seo' => new SeoData(
                title: (filled($release->credit) ? "{$release->title} by {$release->credit}" : $release->title).' - '.SeoData::SITE_NAME,
                description: $release->summary(),
                path: $path,
                image: $release->coverUrl(),
                imageIsThumbnail: ! $release->hasUploadedArtwork(),
                type: 'music.album',
                schema: [
                    Schema::breadcrumbs($trail),
                    Schema::musicAlbum($release),
                ],
            ),
        ]);
    }

    /**
     * Other releases by any of the release's roster artists, newest first.
     *
     * @return Collection<int, Release>
     */
    private function moreReleasesLike(Release $release): Collection
    {
        if ($release->artists->isEmpty()) {
            return new Collection;
        }

        return Release::query()
            ->whereKeyNot($release->getKey())
            ->whereHas('artists', fn (Builder $query) => $query->whereKey($release->artists->modelKeys()))
            ->with('artists')
            ->newestFirst()
            ->limit(self::MORE_RELEASES_LIMIT)
            ->get();
    }
}
