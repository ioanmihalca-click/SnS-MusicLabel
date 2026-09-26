<?php

namespace App\Models;

use App\Enums\DemoStatus;
use App\Enums\Genre;
use Database\Factories\DemoSubmissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * A demo sent through the form at /demos (App\Livewire\DemoForm).
 *
 * Demos the label has not accepted are deleted 12 months after they were
 * sent, by `php artisan model:prune` (scheduled daily in routes/console.php,
 * so it only runs where the scheduler's cron is set up).
 */
class DemoSubmission extends Model
{
    /** @use HasFactory<DemoSubmissionFactory> */
    use HasFactory;

    use Prunable;

    /**
     * How long a demo that was not accepted is kept.
     */
    public const RETENTION_MONTHS = 12;

    /**
     * The `genre` value for music outside the label's genres.
     */
    public const OTHER_GENRE = 'other';

    /**
     * The hosts a private link may point to: SoundCloud, Dropbox and Google
     * Drive. `www.` in front of them is accepted too.
     *
     * @var list<string>
     */
    public const LINK_HOSTS = ['soundcloud.com', 'on.soundcloud.com', 'dropbox.com', 'drive.google.com'];

    protected $fillable = [
        'artist_name',
        'email',
        'link',
        'genre',
        'country',
        'message',
        'rights_confirmed',
        'status',
        'notes',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'new',
        'rights_confirmed' => false,
    ];

    protected function casts(): array
    {
        return [
            'rights_confirmed' => 'boolean',
            'status' => DemoStatus::class,
        ];
    }

    /**
     * The label's genres, then "Other", keyed by the stored value.
     *
     * @return array<string, string>
     */
    public static function genreOptions(): array
    {
        return collect(Genre::cases())
            ->mapWithKeys(fn (Genre $genre): array => [$genre->value => $genre->getLabel()])
            ->put(self::OTHER_GENRE, 'Other')
            ->all();
    }

    /**
     * Whether the URL is an https link to SoundCloud, Dropbox or Google Drive.
     */
    public static function isAllowedLink(string $url): bool
    {
        $parts = parse_url(trim($url));

        if ($parts === false || strtolower($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['port'])) {
            return false;
        }

        $host = (string) preg_replace('/^www\./', '', strtolower($parts['host'] ?? ''));

        return in_array($host, self::LINK_HOSTS, true);
    }

    public function genreLabel(): ?string
    {
        if (blank($this->genre)) {
            return null;
        }

        return self::genreOptions()[$this->genre] ?? $this->genre;
    }

    /**
     * Demos older than the retention period that the label did not accept.
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        return static::query()
            ->where('created_at', '<=', now()->subMonths(self::RETENTION_MONTHS))
            ->whereNot('status', DemoStatus::Accepted->value);
    }
}
