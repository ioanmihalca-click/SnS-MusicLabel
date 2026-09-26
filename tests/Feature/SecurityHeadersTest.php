<?php

it('sends the basic security headers, without a Content-Security-Policy', function (string $uri) {
    $response = $this->get($uri);

    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()')
        ->assertHeaderMissing('Content-Security-Policy');
})->with([
    'page' => '/',
    'Markdown version' => '/index.md',
    'robots.txt' => '/robots.txt',
    'admin panel' => '/admin/login',
]);
