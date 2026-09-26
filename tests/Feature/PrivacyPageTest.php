<?php

use Dom\Element;

it('names the controller from the configuration, without an empty address line', function () {
    config([
        'site.privacy.controller_name' => 'Snow N Stuff AB',
        'site.privacy.controller_address' => null,
        'site.privacy.contact_email' => 'privacy@example.com',
    ]);

    $section = htmlDocument($this->get('/privacy')->assertOk())->querySelector('#controller');

    expect($section->querySelector('dl')->textContent)->toContain('Snow N Stuff AB')
        ->not->toContain('Address')
        ->and($section->querySelector('a[href="mailto:privacy@example.com"]'))->not->toBeNull();

    config(['site.privacy.controller_address' => 'Storgatan 1, 111 22 Stockholm, Sweden']);

    $this->get('/privacy')->assertSeeInOrder(['Address', 'Storgatan 1, 111 22 Stockholm, Sweden']);
});

it('covers what article 13 GDPR asks for, in order', function () {
    $headings = iterator_to_array(htmlDocument($this->get('/privacy')->assertOk())->querySelectorAll('main section[id] > h2'), false);

    expect(array_map(fn (Element $heading): string => $heading->textContent, $headings))->toBe([
        'Who is responsible',
        'What we collect and why',
        'Who receives it',
        'Transfers outside the EU',
        'How long we keep it',
        'Your rights',
        'Complaints',
        'Cookies',
        'Changes to this policy',
    ]);

    $this->get('/privacy')->assertSeeText('Art. 6(1)(a) GDPR')
        ->assertSeeText('Art. 6(1)(b) GDPR')
        ->assertSeeText('Art. 6(1)(f) GDPR')
        ->assertSeeText('EU-US Data Privacy Framework')
        ->assertSeeText('IMY')
        ->assertSeeText('ANSPDCP')
        ->assertSeeText('up to 12 months');
});

it('lists every cookie with its provider, purpose, duration and category', function () {
    config([
        'session.cookie' => 'snow_n_stuff_session',
        'session.lifetime' => 120,
        'services.google_analytics.id' => 'G-1PQQSTPYZC',
    ]);

    $rows = iterator_to_array(htmlDocument($this->get('/privacy')->assertOk())->querySelectorAll('#cookies tbody tr'), false);
    $cells = array_map(
        fn (Element $row): array => array_map(fn (Element $cell): string => trim($cell->textContent), iterator_to_array($row->querySelectorAll('th, td'), false)),
        $rows,
    );

    expect(array_column($cells, 0))->toBe([
        'snow_n_stuff_session', 'XSRF-TOKEN', 'sns.consent (local storage)', '_ga', '_ga_1PQQSTPYZC', 'sp_t', 'sp_landing', 'Beatport cookies', 'nfan.link cookies',
    ])
        ->and($cells[0])->toBe(['snow_n_stuff_session', 'This site', 'Keeps your session, e.g. while you fill in the demo form.', '2 hours', 'Necessary'])
        ->and(array_count_values(array_column($cells, 4)))->toBe(['Necessary' => 3, 'Analytics' => 2, 'External media' => 4]);
});

it('ends with the version date and a Cookie settings button', function () {
    $document = htmlDocument($this->get('/privacy')->assertOk());

    expect(trim($document->querySelector('main #cookies button[data-consent-open]')->textContent))->toBe('Cookie settings')
        ->and($document->querySelector('#changes time')->getAttribute('datetime'))->toBe('2026-09-26');
});

it('marks the text as a draft until the site runs in production', function () {
    $this->get('/privacy')->assertSeeText('Draft — to be reviewed by the client/legal counsel before launch.');

    app()->detectEnvironment(fn (): string => 'production');

    $this->get('/privacy')->assertDontSeeText('Draft — to be reviewed');
});

it('gives agents clean Markdown, the cookie list as a table', function () {
    config(['session.cookie' => 'snow_n_stuff_session']);

    $markdown = $this->get('/privacy.md')->assertOk()->getContent();

    expect($markdown)
        ->toStartWith("# Privacy & cookies\n")
        ->toContain("| Name | Provider | Purpose | Duration | Category |\n|---|---|---|---|---|\n| snow\\_n\\_stuff\\_session | This site |")
        ->toContain("## Your rights\n")
        ->not->toContain('<')
        ->not->toContain('On this page');
});
