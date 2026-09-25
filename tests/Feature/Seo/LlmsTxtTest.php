<?php

use App\Models\Artist;
use App\Models\Blog;
use App\Models\Release;

it('opens with the site name and a one-paragraph summary', function () {
    $response = $this->get('/llms.txt');

    $response->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    expect($response->getContent())->toStartWith("# Snow 'n' Stuff\n\n> ");
});

it('links every public page to its Markdown version', function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
    Blog::factory()->published()->create([
        'title' => 'Speak To Me [Out Now]',
        'slug' => 'speak-to-me',
        'meta_description' => 'Our new single is out.',
    ]);
    Blog::factory()->scheduled()->create(['slug' => 'scheduled-post']);

    $this->get('/llms.txt')
        ->assertOk()
        ->assertSee("## Pages\n\n- [Home](https://snow-n-stuff.com/index.md): ", escape: false)
        ->assertSee("## Blog\n\n- [Blog](https://snow-n-stuff.com/blog.md): ", escape: false)
        ->assertSee('- [Speak To Me \[Out Now\]](https://snow-n-stuff.com/blog/speak-to-me.md): Our new single is out.', escape: false)
        ->assertDontSee('scheduled-post');
});

it('lists the catalogue pages under their own headings', function () {
    config(['app.url' => 'https://snow-n-stuff.com']);
    Artist::factory()->create(['name' => 'G&S', 'description' => '<p>A DJ and producer duo.</p>']);
    Release::factory()->create(['title' => 'Back to Black', 'artist_display' => 'G&S', 'description' => '<p>A deep house remake.</p>']);

    $this->get('/llms.txt')
        ->assertOk()
        ->assertSee('- [About](https://snow-n-stuff.com/about.md): ', escape: false)
        ->assertSee('- [Playlists](https://snow-n-stuff.com/playlists.md): ', escape: false)
        ->assertSee("## Releases\n\n- [Releases](https://snow-n-stuff.com/releases.md): ", escape: false)
        ->assertSee('- [Back to Black by G&S](https://snow-n-stuff.com/releases/back-to-black.md): A deep house remake.', escape: false)
        ->assertSee("## Artists\n\n- [Artists](https://snow-n-stuff.com/artists.md): ", escape: false)
        ->assertSee('- [G&S](https://snow-n-stuff.com/artists/g-and-s.md): A DJ and producer duo.', escape: false);
});
