<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use App\Support\NewsItem;
use App\Support\Seo\SeoData;
use App\Support\Spotify\SpotifyThumbnail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public const LATEST_RELEASES = 8;

    public const PHOTOS = 6;

    /**
     * The homepage: the featured release, the newest releases, DJ support and
     * charts, the roster, the playlists, a short about with photos, the news
     * and where to follow the label. The Spotify thumbnails that stand in for
     * missing artwork are looked up all at once, before rendering.
     *
     * It is also routed as `/index` so that its Markdown version lives at
     * `/index.md`; any other request for `/index` goes to `/`.
     */
    public function __invoke(Request $request, SpotifyThumbnail $thumbnails): View|RedirectResponse
    {
        if ($request->routeIs('home.index') && ! $this->isMarkdownSuffixRequest($request)) {
            return to_route('home', status: 301);
        }

        // The catalogue is small (a few dozen releases): loaded once for the hero,
        // the grid, its count and the support band.
        $releases = Release::query()->with('artists')->newestFirst()->get();
        $heroRelease = $releases->firstWhere('is_featured', true) ?? $releases->first();
        $heroRelease?->load('tracks');
        $nextRelease = $releases->first(fn (Release $release): bool => ! $release->is($heroRelease));
        $latestReleases = $releases->take(self::LATEST_RELEASES);
        $artists = Artist::query()->inRosterOrder()->withCount('releases')->get();
        $playlists = Playlist::query()->active()->get();
        $newsEntries = NewsItem::latestEntries();

        $thumbnails->warmArtwork([
            $heroRelease,
            $nextRelease,
            ...$latestReleases,
            ...$newsEntries->whereInstanceOf(Release::class),
            ...$artists,
            ...$playlists,
        ]);

        return view('home', [
            'heroRelease' => $heroRelease,
            'nextRelease' => $nextRelease,
            'releases' => $releases,
            'latestReleases' => $latestReleases,
            'artists' => $artists,
            'playlists' => $playlists,
            'photos' => Photo::query()->latest()->limit(self::PHOTOS)->get(),
            'news' => $newsEntries->map(NewsItem::from(...)),
            'seo' => new SeoData(
                title: SeoData::DEFAULT_TITLE,
                description: SeoData::DEFAULT_DESCRIPTION,
                path: '/',
            ),
        ]);
    }

    /**
     * Whether spatie/laravel-markdown-response stripped a `.md` suffix from the URL.
     */
    private function isMarkdownSuffixRequest(Request $request): bool
    {
        return (bool) $request->attributes->get('markdown-response.suffix');
    }
}
