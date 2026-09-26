<?php

use App\Enums\DemoStatus;
use App\Livewire\DemoForm;
use App\Models\DemoSubmission;
use App\Notifications\NewDemoSubmission;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
    $this->withoutDefer();
});

/**
 * The form filled in as an artist would, a few seconds after the page loaded.
 *
 * @param  array<string, mixed>  $overrides
 */
function filledDemoForm(array $overrides = []): Testable
{
    $component = Livewire::test(DemoForm::class);

    foreach ([
        'artistName' => 'G&S',
        'email' => 'gands@example.com',
        'link' => 'https://soundcloud.com/gands/back-to-black/s-AbCdEfGhIjK',
        'genre' => 'deep-house',
        'country' => 'Sweden',
        'message' => 'A deep house remake, played by Richie Hawtin.',
        'rightsConfirmed' => true,
        ...$overrides,
    ] as $property => $value) {
        $component->set($property, $value);
    }

    test()->travel(DemoForm::MINIMUM_FILL_MILLISECONDS + 1000)->milliseconds();

    return $component;
}

it('saves the demo as new, notifies the label and thanks the artist', function () {
    config(['services.demos.notify_email' => 'a-and-r@example.com']);

    filledDemoForm(['artistName' => '  G&S   Collective ', 'country' => ''])
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('isSent', true)
        ->assertSet('artistName', '')
        ->assertSee(["Thanks, it's in", "We listen to every demo. If we want to take it further, we'll get in touch."], escape: false);

    $demo = DemoSubmission::sole();

    expect($demo)
        ->artist_name->toBe('G&S Collective')
        ->email->toBe('gands@example.com')
        ->link->toBe('https://soundcloud.com/gands/back-to-black/s-AbCdEfGhIjK')
        ->genre->toBe('deep-house')
        ->country->toBeNull()
        ->rights_confirmed->toBeTrue()
        ->status->toBe(DemoStatus::New);

    Notification::assertSentOnDemand(
        NewDemoSubmission::class,
        fn (NewDemoSubmission $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'a-and-r@example.com'
            && $notification->submission->is($demo),
    );
    Notification::assertSentOnDemandTimes(NewDemoSubmission::class, 1);
});

it('writes the notification so the label can listen, review and reply', function () {
    filledDemoForm()->call('submit');

    $demo = DemoSubmission::sole();
    $mail = (new NewDemoSubmission($demo))->toMail(new AnonymousNotifiable);

    expect($mail->subject)->toBe('New demo: G&S')
        ->and($mail->replyTo)->toBe([['gands@example.com', 'G&S']])
        ->and($mail->actionUrl)->toEndWith("/admin/demo-submissions/{$demo->id}/edit")
        ->and(implode("\n", $mail->introLines))->toContain('https://soundcloud.com/gands/back-to-black/s-AbCdEfGhIjK', 'Deep House', 'Sweden');
});

it('requires the artist, the email, the link and the rights confirmation', function () {
    filledDemoForm(['artistName' => '', 'email' => '', 'link' => '', 'rightsConfirmed' => false])
        ->call('submit')
        ->assertHasErrors(['artistName' => 'required', 'email' => 'required', 'link' => 'required', 'rightsConfirmed' => 'accepted'])
        ->assertSet('isSent', false);

    expect(DemoSubmission::count())->toBe(0);
    Notification::assertNothingSent();
});

it('rejects an invalid email address and an unknown genre', function () {
    filledDemoForm(['email' => 'not-an-email', 'genre' => 'polka'])
        ->call('submit')
        ->assertHasErrors(['email' => 'email', 'genre' => 'in']);

    expect(DemoSubmission::count())->toBe(0);
});

it('accepts private links from SoundCloud, Dropbox and Google Drive', function (string $link) {
    filledDemoForm(['link' => $link])->call('submit')->assertHasNoErrors();

    expect(DemoSubmission::sole()->link)->toBe($link);
})->with([
    'SoundCloud' => 'https://soundcloud.com/gands/back-to-black/s-AbCdEfGhIjK',
    'SoundCloud short link' => 'https://on.soundcloud.com/AbCdEfGh',
    'Dropbox' => 'https://www.dropbox.com/scl/fi/abc123/back-to-black.wav?rlkey=xyz&dl=0',
    'Google Drive' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view?usp=sharing',
]);

it('rejects links that are not https or not from SoundCloud, Dropbox or Google Drive', function (string $link) {
    filledDemoForm(['link' => $link])->call('submit')->assertHasErrors('link');

    expect(DemoSubmission::count())->toBe(0);
})->with([
    'plain http' => 'http://soundcloud.com/gands/back-to-black',
    'another service' => 'https://wetransfer.com/downloads/abc',
    'lookalike host' => 'https://soundcloud.com.evil.example/gands',
    'host in the path' => 'https://evil.example/soundcloud.com/gands',
    'credentials before the host' => 'https://soundcloud.com@evil.example/gands',
    'not a URL' => 'soundcloud gands back to black',
    'too long' => 'https://soundcloud.com/'.str_repeat('a', 2030),
]);

it('discards a submission whose hidden field is filled in, as if it were sent', function () {
    filledDemoForm(['website' => 'https://spam.example'])
        ->call('submit')
        ->assertSet('isSent', true);

    expect(DemoSubmission::count())->toBe(0);
    Notification::assertNothingSent();
});

it('rejects a submission sent within three seconds of the form appearing', function () {
    $component = Livewire::test(DemoForm::class)
        ->set('artistName', 'G&S')
        ->set('email', 'gands@example.com')
        ->set('link', 'https://soundcloud.com/gands/back-to-black')
        ->set('rightsConfirmed', true);

    $this->travel(2)->seconds();

    $component->call('submit')
        ->assertHasErrors('form')
        ->assertSee('That was quick.')
        ->assertSet('isSent', false);

    expect(DemoSubmission::count())->toBe(0);
});

it('keeps the time the form appeared out of the browser\'s reach', function () {
    Livewire::test(DemoForm::class)->set('shownAt', 0);
})->throws(CannotUpdateLockedPropertyException::class);

it('accepts three demos an hour from one IP address', function () {
    foreach (['one', 'two', 'three'] as $name) {
        filledDemoForm(['email' => "{$name}@example.com"])->call('submit')->assertHasNoErrors();
    }

    filledDemoForm(['email' => 'four@example.com'])
        ->call('submit')
        ->assertHasErrors('form')
        ->assertSee('You have reached the limit of 3 demos an hour. Please try again in');

    expect(DemoSubmission::count())->toBe(3);
    Notification::assertSentOnDemandTimes(NewDemoSubmission::class, 3);

    $this->travel(61)->minutes();

    filledDemoForm(['email' => 'four@example.com'])->call('submit')->assertHasNoErrors();

    expect(DemoSubmission::count())->toBe(4);
});

it('accepts three demos an hour for one email address, from any IP address', function () {
    foreach (range(1, DemoForm::MAX_SUBMISSIONS_PER_HOUR) as $attempt) {
        RateLimiter::hit(DemoForm::rateLimitKey('email', 'gands@example.com'), 3600);
    }

    filledDemoForm(['email' => 'GANDS@example.com'])->call('submit')->assertHasErrors('form');
    filledDemoForm(['email' => 'thk@example.com'])->call('submit')->assertHasNoErrors();

    expect(DemoSubmission::pluck('email')->all())->toBe(['thk@example.com']);
});

it('logs a notification that fails and still thanks the artist', function () {
    Log::spy();
    Notification::shouldReceive('send')->andThrow(new RuntimeException('Mail server down'));

    filledDemoForm()->call('submit')->assertHasNoErrors()->assertSet('isSent', true);

    $demo = DemoSubmission::sole();

    Log::shouldHaveReceived('error')->once()->withArgs(fn (string $message, array $context): bool => $context['demo_submission_id'] === $demo->id
        && $context['exception']->getMessage() === 'Mail server down');
});

it('offers an empty form for another demo', function () {
    filledDemoForm()
        ->call('submit')
        ->call('sendAnother')
        ->assertSet('isSent', false)
        ->assertSee('Send demo')
        ->assertSet('link', '');
});

it('shows the form on /demos with every field labelled, the trap hidden and the privacy policy linked', function () {
    $document = htmlDocument($this->get('/demos')->assertOk());

    foreach (['demo-artist', 'demo-email', 'demo-link', 'demo-genre', 'demo-country', 'demo-message', 'demo-rights'] as $id) {
        expect($document->querySelector("label[for=\"{$id}\"]"))->not->toBeNull("#{$id} has no label");
    }

    $genres = array_map(fn ($option): string => $option->getAttribute('value'), iterator_to_array($document->querySelectorAll('#demo-genre option'), false));

    expect(trim($document->querySelector('h1')->textContent))->toBe('Demos')
        ->and($genres)->toBe(['', 'tech-house', 'deep-house', 'house', 'techno', 'melodic-techno', 'afro-house', 'other'])
        ->and($document->querySelector('#demo-website')->getAttribute('tabindex'))->toBe('-1')
        ->and($document->querySelector('#demo-website')->closest('[aria-hidden="true"]'))->not->toBeNull()
        ->and($document->querySelector('#demo-rights')->closest('label')->textContent)->toContain('I own or control all rights to this music and any samples are cleared.')
        ->and($document->querySelector('main form a[href="'.route('privacy').'"][wire\:navigate]'))->not->toBeNull();
});

it('gives agents the guidelines without the form', function () {
    $markdown = $this->get('/demos.md')->assertOk()->getContent();

    expect($markdown)
        ->toStartWith("# Demos\n")
        ->toContain('SoundCloud, Dropbox or Google Drive')
        ->toContain("We listen to every demo. If we want to take it further, we'll get in touch.")
        ->not->toContain('<')
        ->not->toContain('Send demo')
        ->not->toContain('Website');
});
