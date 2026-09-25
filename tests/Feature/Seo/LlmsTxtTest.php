<?php

use App\Models\Blog;

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
