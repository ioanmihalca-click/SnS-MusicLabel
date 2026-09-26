<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Playlist;
use App\Models\Release;
use App\Support\Seo\PublicPage;
use App\Support\Seo\PublicPages;
use Dom\Element;

beforeEach(function () {
    fakeSpotifyThumbnails();
});

it('offers Accept all, Reject all and Customize alike on the first level, with the privacy policy', function () {
    $banner = htmlDocument($this->get('/')->assertOk())->querySelector('[data-consent-banner]');
    $buttons = iterator_to_array($banner->querySelectorAll('[data-consent-view="notice"] button'), false);

    expect(array_map(fn (Element $button): string => trim($button->textContent), $buttons))->toBe(['Accept all', 'Reject all', 'Customize'])
        ->and(array_unique(array_map(fn (Element $button): string => $button->getAttribute('class'), $buttons)))->toHaveCount(1)
        ->and(array_map(fn (Element $button): string => $button->getAttribute('data-consent-action'), $buttons))->toBe(['accept', 'reject', 'customize'])
        ->and($banner->querySelector('[data-consent-view="notice"] a[href="'.route('privacy').'"][wire\:navigate]'))->not->toBeNull()
        ->and($banner->getAttribute('aria-modal'))->toBe('false');
});

it('lists the three categories in the settings, with only Necessary switched on and locked', function () {
    $settings = htmlDocument($this->get('/')->assertOk())->querySelector('[data-consent-banner] [data-consent-view="settings"]');
    $switch = fn (string $id): Element => $settings->querySelector("input#consent-{$id}[type=\"checkbox\"][role=\"switch\"]");

    expect($settings->hasAttribute('hidden'))->toBeTrue()
        ->and($switch('necessary')->hasAttribute('checked'))->toBeTrue()
        ->and($switch('necessary')->hasAttribute('disabled'))->toBeTrue()
        ->and($switch('analytics')->hasAttribute('checked'))->toBeFalse()
        ->and($switch('analytics')->getAttribute('data-consent-choice'))->toBe('analytics')
        ->and($switch('media')->hasAttribute('checked'))->toBeFalse()
        ->and($switch('media')->getAttribute('data-consent-choice'))->toBe('media')
        ->and($settings->querySelector('label[for="consent-media"]')->textContent)->toBe('External media')
        ->and(trim($settings->querySelector('[data-consent-action="save"]')->textContent))->toBe('Save choices');
});

it('puts Privacy and Cookie settings in the footer', function () {
    $footer = htmlDocument($this->get('/')->assertOk())->querySelector('body > footer');

    expect($footer->querySelector('a[href="'.route('privacy').'"][wire\:navigate]')->textContent)->toBe('Privacy')
        ->and(trim($footer->querySelector('button[data-consent-open]')->textContent))->toBe('Cookie settings');
});

it('serves no Google Analytics script and no third-party player on any page, in production too', function () {
    app()->detectEnvironment(fn (): string => 'production');
    $artist = Artist::factory()->create(['name' => 'G&S']);
    Release::factory()->featured()->create(['spotify_url' => 'https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N'])->artists()->attach($artist);
    Playlist::factory()->create();
    Blog::factory()->published()->create([
        'content' => '<p><iframe src="https://open.spotify.com/embed/album/3zifCl5R2DaZGEmrPNUM1N"></iframe></p>'
            .'<p><iframe src="https://embed.beatport.com/?id=20186693&amp;type=track"></iframe></p>'
            .'<p><iframe src="https://nfan.link/snow-n-stuff"></iframe></p>',
    ]);

    foreach (app(PublicPages::class)->all() as $page) {
        /** @var PublicPage $page */
        $html = $this->get($page->path)->assertOk()->getContent();
        $document = htmlDocument($html);

        expect($document->querySelectorAll('iframe'))->toHaveCount(0, "{$page->path} has an iframe")
            ->and($html)->not->toContain('googletagmanager.com')
            ->and($html)->not->toContain('gtag(')
            ->and($document->querySelectorAll('meta[name="sns-ga-id"]'))->toHaveCount(1);
    }
});
