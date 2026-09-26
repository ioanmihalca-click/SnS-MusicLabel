<?php

use App\Filament\Resources\BlogResource;
use App\Filament\Resources\BlogResource\Pages\CreateBlog;
use App\Filament\Resources\BlogResource\Pages\EditBlog;
use App\Filament\Resources\BlogResource\Pages\ListBlogs;
use App\Models\Blog;
use App\Models\User;
use App\Support\Seo\PageCache;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'contact@snow-n-stuff.com']);
    $this->actingAs($this->admin);
});

it('renders the blogs index', function () {
    Blog::factory()->count(2)->create();

    $this->get('/admin/blogs')->assertOk();
});

it('renders the create blog page', function () {
    $this->get('/admin/blogs/create')->assertOk();
});

it('renders the edit blog page', function () {
    $blog = Blog::factory()->create();

    $this->get("/admin/blogs/{$blog->id}/edit")->assertOk();
});

it('preserves the existing slug across title edits', function () {
    $blog = Blog::factory()->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
    ]);

    $blog->title = 'Brand New Title';
    $blog->save();

    expect($blog->fresh()->slug)->toBe('original-title');
});

it('auto-generates a slug for new posts when none is provided', function () {
    $blog = Blog::create([
        'title' => 'Fresh Article',
        'content' => '<p>body</p>',
        'published_at' => now()->subDay(),
    ]);

    expect($blog->slug)->toBe('fresh-article');
});

it('warns that saving a post with embedded players removes them', function () {
    $blog = Blog::factory()->create([
        'content' => '<p>Out now.</p><p><iframe src="https://open.spotify.com/embed/album/3zifCl5R2DaZGEmrPNUM1N"></iframe></p>',
    ]);

    Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
        ->assertSee('Embedded players')
        ->assertSee(BlogResource::EMBEDS_WARNING);
});

it('shows no warning for posts without embedded players', function () {
    $blog = Blog::factory()->create(['content' => '<p>Out now on <a href="https://open.spotify.com/album/3zifCl5R2DaZGEmrPNUM1N">Spotify</a>.</p>']);

    Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
        ->assertDontSee(BlogResource::EMBEDS_WARNING);
    Livewire::test(CreateBlog::class)
        ->assertDontSee(BlogResource::EMBEDS_WARNING);
});

describe('publishing', function () {
    beforeEach(function () {
        $this->travelTo('2026-09-27 10:15:42');
        Cache::store(PageCache::STORE)->put('sitemap.xml', 'stale');
    });

    it('publishes a draft or a scheduled post now from its table row', function (?string $publishedAt) {
        $blog = Blog::factory()->create(['published_at' => $publishedAt]);
        Cache::store(PageCache::STORE)->put('sitemap.xml', 'stale');

        Livewire::test(ListBlogs::class)
            ->assertTableActionHidden('unpublish', $blog)
            ->callTableAction('publishNow', $blog)
            ->assertNotified('Published');

        expect($blog->fresh()->published_at->toDateTimeString())->toBe('2026-09-27 10:15:42')
            ->and(Cache::store(PageCache::STORE)->has('sitemap.xml'))->toBeFalse();
    })->with([
        'draft' => [null],
        'scheduled' => ['2026-10-04 12:00:00'],
    ]);

    it('unpublishes a live post from its table row, after confirmation', function () {
        $blog = Blog::factory()->published()->create();
        Cache::store(PageCache::STORE)->put('sitemap.xml', 'stale');

        Livewire::test(ListBlogs::class)
            ->assertTableActionHidden('publishNow', $blog)
            ->mountTableAction('unpublish', $blog)
            ->assertTableActionMounted('unpublish')
            ->callMountedTableAction()
            ->assertNotified('Unpublished');

        expect($blog->fresh()->published_at)->toBeNull()
            ->and(Cache::store(PageCache::STORE)->has('sitemap.xml'))->toBeFalse();
    });

    it('saves the form and publishes the post now from the edit page', function () {
        $blog = Blog::factory()->scheduled()->create(['title' => 'Speak To Me']);

        Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
            ->assertActionHidden('unpublish')
            ->fillForm(['title' => 'Speak To Me (Out Now)'])
            ->callAction('publishNow')
            ->assertHasNoFormErrors()
            ->assertNotified('Published')
            ->assertFormSet(['published_at' => '2026-09-27 10:15'])
            ->assertActionHidden('publishNow')
            ->assertActionVisible('unpublish');

        expect($blog->fresh())
            ->title->toBe('Speak To Me (Out Now)')
            ->published_at->toDateTimeString()->toBe('2026-09-27 10:15:42');
    });

    it('validates the form before publishing from the edit page', function () {
        $blog = Blog::factory()->draft()->create(['title' => 'Speak To Me']);

        Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
            ->fillForm(['title' => ''])
            ->callAction('publishNow')
            ->assertHasFormErrors(['title' => 'required']);

        expect($blog->fresh())
            ->title->toBe('Speak To Me')
            ->published_at->toBeNull();
    });

    it('asks before publishing a post whose embedded players the save removes', function () {
        $blog = Blog::factory()->draft()->create([
            'content' => '<p><iframe src="https://open.spotify.com/embed/album/3zifCl5R2DaZGEmrPNUM1N"></iframe></p>',
        ]);

        $page = Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
            ->mountAction('publishNow')
            ->assertActionMounted('publishNow');

        expect($blog->fresh()->published_at)->toBeNull();

        $page->callMountedAction()->assertHasNoFormErrors();

        expect($blog->fresh()->published_at)->not->toBeNull();
    });

    it('unpublishes the post from the edit page, after confirmation', function () {
        $blog = Blog::factory()->published()->create();

        Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
            ->assertActionHidden('publishNow')
            ->mountAction('unpublish')
            ->assertActionMounted('unpublish')
            ->callMountedAction()
            ->assertNotified('Unpublished')
            ->assertFormSet(['published_at' => null])
            ->assertActionVisible('publishNow')
            ->assertActionHidden('unpublish');

        expect($blog->fresh()->published_at)->toBeNull();
    });

    it('creates a published post with "Publish now", whatever the date field says', function () {
        Livewire::test(CreateBlog::class)
            ->fillForm([
                'title' => 'Speak To Me',
                'content' => '<p>Out now.</p>',
                'published_at' => '2026-12-01 09:00',
            ])
            ->callAction('publishNow')
            ->assertHasNoFormErrors()
            ->assertNotified('Published')
            ->assertRedirect();

        expect(Blog::sole())
            ->title->toBe('Speak To Me')
            ->published_at->toDateTimeString()->toBe('2026-09-27 10:15:42')
            ->isPublished()->toBeTrue()
            ->and(Cache::store(PageCache::STORE)->has('sitemap.xml'))->toBeFalse();
    });

    it('validates the post before creating it with "Publish now"', function () {
        Livewire::test(CreateBlog::class)
            ->fillForm(['content' => '<p>Out now.</p>'])
            ->callAction('publishNow')
            ->assertHasFormErrors(['title' => 'required']);

        expect(Blog::count())->toBe(0);
    });

    it('keeps "Create" as a draft when the date field is empty', function () {
        Livewire::test(CreateBlog::class)
            ->fillForm(['title' => 'Speak To Me', 'content' => '<p>Out now.</p>'])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Blog::sole()->published_at)->toBeNull();
    });

    it('fills the date field with the current date and time', function () {
        Livewire::test(CreateBlog::class)
            ->assertFormSet(['published_at' => null])
            ->callFormComponentAction('published_at', 'setPublishDateToNow')
            ->assertFormSet(['published_at' => '2026-09-27 10:15']);
    });
});

describe('homepage hero', function () {
    beforeEach(function () {
        $this->travelTo('2026-09-27 10:15:42');
    });

    it('saves the hero date and text', function () {
        $blog = Blog::factory()->published()->create();

        Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
            ->fillForm([
                'hero_until' => '2026-10-25 18:00',
                'hero_text' => 'Meet us at ADE 2026 · Amsterdam, 21–25 October',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($blog->fresh())
            ->hero_until->toDateTimeString()->toBe('2026-10-25 18:00:00')
            ->hero_text->toBe('Meet us at ADE 2026 · Amsterdam, 21–25 October')
            ->isInHero()->toBeTrue();
    });

    it('keeps the hero text short', function () {
        $blog = Blog::factory()->published()->create();

        Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
            ->fillForm(['hero_text' => str_repeat('a', 81)])
            ->call('save')
            ->assertHasFormErrors(['hero_text' => 'max']);
    });

    it('marks the posts in the hero right now in the table', function () {
        $inHero = Blog::factory()->published()->inHero()->create();
        $expired = Blog::factory()->published()->create(['hero_until' => now()->subMinute()]);
        $scheduled = Blog::factory()->scheduled()->inHero()->create();

        Livewire::test(ListBlogs::class)
            ->assertTableColumnFormattedStateSet('in_hero', 'Until Oct 4, 10:15', $inHero)
            ->assertTableColumnStateSet('in_hero', null, $expired)
            ->assertTableColumnStateSet('in_hero', null, $scheduled);
    });
});
