<?php

use App\Livewire\PhotoGallery;
use App\Models\Photo;

it('keeps the full story, the gallery and every contact', function () {
    Photo::factory()->create(['title' => 'Snow n Stuff in Ibiza', 'image_path' => 'photos/ibiza.jpg']);

    $this->get('/about')
        ->assertOk()
        ->assertSeeText('Management, Label and Music Production')
        ->assertSeeText('Management for: THK, G&amp;S, Snow \'n\' Stuff and Style Da Kid among others.', escape: false)
        ->assertSeeText('With over two and a half decades dedicated to the art of Artists and Repertoire (A&amp;R)', escape: false)
        ->assertSeeText('Garnering Grammy nominations')
        ->assertSeeText('Snow \'n\' Stuff is actively creating immersive live events and experiences', escape: false)
        ->assertSeeLivewire(PhotoGallery::class)
        ->assertSee('id="gallery"', escape: false)
        ->assertSee('alt="Snow n Stuff in Ibiza"', escape: false)
        ->assertSee('data-fancybox="gallery"', escape: false)
        ->assertSeeInOrder(['Contact Us', 'Stockholm &amp; Romania'], escape: false)
        ->assertSeeInOrder(['Bookings, Remix and Sync Requests', 'glenn@1namm.com', 'Licensing/Booking', 'info@1namm.com', 'Demo', 'demo@1namm.com', 'Web Development', 'contact@clickstudios-digital.com'])
        ->assertSee('href="https://www.linkedin.com/in/glenn-forrestgate-457228a9"', escape: false)
        ->assertSeeText('Follow us on social media to stay updated with our latest releases, events, and artist news.');
});

it('reads the story in Markdown with the entities decoded', function () {
    $markdown = $this->get('/about.md')->assertOk()->getContent();

    expect($markdown)
        ->toContain('# About')
        ->toContain('Management for: THK, G&S,')
        ->toContain('Bookings, Remix and Sync Requests: glenn@1namm.com')
        ->not->toContain('&amp;');
});
