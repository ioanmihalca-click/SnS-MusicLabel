<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Release;
use App\Models\User;
use Database\Seeders\SnapshotSeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('refuses to run in production', function () {
    app()->detectEnvironment(fn (): string => 'production');

    expect(fn () => app(SnapshotSeeder::class)->run())
        ->toThrow(RuntimeException::class, 'must never run in production');

    expect(Release::count())->toBe(0)
        ->and(User::count())->toBe(0);
});

it('recreates the public site content through the real backfill', function () {
    Storage::fake('public');
    Http::fake([
        'snow-n-stuff.com/storage/*' => Http::response('image-bytes'),
    ]);

    $this->seed(SnapshotSeeder::class);

    expect(Release::count())->toBe(19)
        ->and(Artist::count())->toBe(4)
        ->and(Playlist::whereNotNull('title')->count())->toBe(5)
        ->and(Blog::count())->toBe(10)
        ->and(Photo::count())->toBe(8);

    expect(Release::featured()->sole())
        ->title->toBe('Human Made')
        ->cover_image->toBe('featured-tracks/01KR785MPC9B3B7E7K37RYX2MK.jpg')
        ->credit->toBe('Snow N Stuff');

    $theChange = Release::query()->where('slug', 'the-change')->sole();
    expect($theChange->title)->toBe('The Change')
        ->and($theChange->tracks->pluck('duration')->all())->toBe(['4:40'])
        ->and($theChange->artists->pluck('slug')->all())->toBe(['snow-n-stuff']);

    expect(Artist::query()->orderBy('slug')->pluck('name')->all())
        ->toBe(['G&S', 'Snow N Stuff', 'Style da Kid', 'THK']);

    Storage::disk('public')->assertExists([
        'featured-tracks/01KR785MPC9B3B7E7K37RYX2MK.jpg',
        'blog-covers/01JQGSZ3QGMYJ9WDJQFK38CM0H.png',
        '01J7DHZG5VRN51R9742C122FSN.jpg',
    ]);

    $admin = User::query()->where('email', 'contact@snow-n-stuff.com')->sole();
    expect(Hash::check('password', $admin->password))->toBeTrue();
});

it('only downloads the images that are missing', function () {
    Storage::fake('public')->put('01J7DHZG5VRN51R9742C122FSN.jpg', 'already here');
    Http::fake([
        'snow-n-stuff.com/storage/*' => Http::response('image-bytes'),
    ]);

    $this->seed(SnapshotSeeder::class);

    Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '01J7DHZG5VRN51R9742C122FSN.jpg'));
    expect(Storage::disk('public')->get('01J7DHZG5VRN51R9742C122FSN.jpg'))->toBe('already here');
});

it('still seeds everything when the live site cannot be reached', function () {
    Storage::fake('public');
    Http::fake([
        'snow-n-stuff.com/*' => Http::failedConnection(),
    ]);

    $this->seed(SnapshotSeeder::class);

    expect(Release::count())->toBe(19)
        ->and(Photo::count())->toBe(8);
    Http::assertSentCount(1);
    Storage::disk('public')->assertMissing('01J7DHZG5VRN51R9742C122FSN.jpg');
});
