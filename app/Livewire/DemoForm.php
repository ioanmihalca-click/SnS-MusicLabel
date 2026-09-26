<?php

namespace App\Livewire;

use App\Models\DemoSubmission;
use App\Notifications\NewDemoSubmission;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

/**
 * The demo form at /demos. Each demo is saved for review in the admin and
 * announced by email to `services.demos.notify_email`, after the response.
 * The artist gets no confirmation email, so the form cannot be used to send
 * mail to others.
 *
 * Spam is kept out without a third-party CAPTCHA: a hidden field that must
 * stay empty, a minimum time between loading the page and sending, measured
 * on the server, and at most three demos an hour per IP address and per
 * email address.
 */
#[Layout('components.layouts.site')]
class DemoForm extends Component
{
    public const DESCRIPTION = "Send your demo to Snow 'n' Stuff: a private SoundCloud, Dropbox or Google Drive link to your unreleased Tech House, Deep House, House or Techno. We listen to every demo.";

    public const MAX_SUBMISSIONS_PER_HOUR = 3;

    public const MINIMUM_FILL_MILLISECONDS = 3000;

    public string $artistName = '';

    public string $email = '';

    public string $link = '';

    public string $genre = '';

    public string $country = '';

    public string $message = '';

    public bool $rightsConfirmed = false;

    /**
     * The trap: hidden from people, so only bots fill it in.
     */
    public string $website = '';

    /**
     * When the form was shown, in milliseconds; set on the server only.
     */
    #[Locked]
    public int $shownAt = 0;

    public bool $isSent = false;

    public function mount(): void
    {
        $this->shownAt = now()->getTimestampMs();
    }

    public function submit(): void
    {
        if (filled($this->website)) {
            // A bot: it is told the demo was sent, and nothing is kept.
            $this->finish();

            return;
        }

        $this->trimInput();
        $validated = $this->validate();

        if (now()->getTimestampMs() - $this->shownAt < self::MINIMUM_FILL_MILLISECONDS) {
            $this->addError('form', 'That was quick. Please check your details, then send the form again.');

            return;
        }

        $keys = [
            self::rateLimitKey('ip', (string) request()->ip()),
            self::rateLimitKey('email', $validated['email']),
        ];

        foreach ($keys as $key) {
            if (RateLimiter::tooManyAttempts($key, self::MAX_SUBMISSIONS_PER_HOUR)) {
                $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));

                $this->addError('form', 'You have reached the limit of '.self::MAX_SUBMISSIONS_PER_HOUR." demos an hour. Please try again in {$minutes} ".Str::plural('minute', $minutes).'.');

                return;
            }
        }

        foreach ($keys as $key) {
            RateLimiter::hit($key, 3600);
        }

        $submission = DemoSubmission::create([
            'artist_name' => $validated['artistName'],
            'email' => $validated['email'],
            'link' => $validated['link'],
            'genre' => $validated['genre'] ?: null,
            'country' => $validated['country'] ?: null,
            'message' => $validated['message'] ?: null,
            'rights_confirmed' => true,
        ]);

        $this->notifyLabel($submission);
        $this->finish();
    }

    /**
     * Back to an empty form, e.g. to send a second track.
     */
    public function sendAnother(): void
    {
        $this->isSent = false;
        $this->shownAt = now()->getTimestampMs();
    }

    /**
     * The rate limiter key for one IP address or email address, hashed so the
     * cache never holds either in clear.
     *
     * @param  'ip'|'email'  $scope
     */
    public static function rateLimitKey(string $scope, string $value): string
    {
        return "demo-submissions:{$scope}:".hash('sha256', Str::lower(trim($value)));
    }

    public function render(): View
    {
        return view('livewire.demo-form', [
            'genres' => DemoSubmission::genreOptions(),
            'trail' => $this->trail(),
        ])->layoutData([
            'seo' => $this->seo(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'artistName' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:254'],
            'link' => [
                'bail',
                'required',
                'string',
                'max:2048',
                'url:https',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! DemoSubmission::isAllowedLink((string) $value)) {
                        $fail('Send a private link from SoundCloud, Dropbox or Google Drive.');
                    }
                },
            ],
            'genre' => ['nullable', 'string', Rule::in(array_keys(DemoSubmission::genreOptions()))],
            'country' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:5000'],
            'rightsConfirmed' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'link.url' => 'Send a private https link from SoundCloud, Dropbox or Google Drive.',
            'rightsConfirmed.accepted' => 'Please confirm that you own or control all rights to this music.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'artistName' => 'artist or project name',
            'link' => 'private link',
        ];
    }

    /**
     * Livewire skips Laravel's TrimStrings middleware, so the text is trimmed here.
     */
    private function trimInput(): void
    {
        $this->artistName = Str::squish($this->artistName);
        $this->email = trim($this->email);
        $this->link = trim($this->link);
        $this->country = Str::squish($this->country);
        $this->message = trim($this->message);
    }

    /**
     * Sent once the response has gone out; a failure is logged and never
     * reaches the artist, whose demo is already saved.
     */
    private function notifyLabel(DemoSubmission $submission): void
    {
        $address = (string) config('services.demos.notify_email');

        if ($address === '') {
            return;
        }

        defer(function () use ($address, $submission): void {
            try {
                Notification::route('mail', $address)->notify(new NewDemoSubmission($submission));
            } catch (Throwable $exception) {
                Log::error('The notification of a new demo could not be sent.', [
                    'demo_submission_id' => $submission->id,
                    'exception' => $exception,
                ]);
            }
        });
    }

    private function finish(): void
    {
        $this->reset('artistName', 'email', 'link', 'genre', 'country', 'message', 'rightsConfirmed', 'website');
        $this->resetValidation();
        $this->isSent = true;
    }

    /**
     * @return array<string, string>
     */
    private function trail(): array
    {
        return ['/' => 'Home', route('demos', absolute: false) => 'Demos'];
    }

    private function seo(): SeoData
    {
        return new SeoData(
            title: 'Send a demo - '.SeoData::SITE_NAME,
            description: self::DESCRIPTION,
            path: route('demos', absolute: false),
            schema: [
                Schema::breadcrumbs($this->trail()),
            ],
        );
    }
}
