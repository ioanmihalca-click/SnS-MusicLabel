<?php

namespace App\Models;

use App\Observers\PublicContentObserver;
use App\Support\Duration;
use Carbon\CarbonInterval;
use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(PublicContentObserver::class)]
class Track extends Model
{
    /** @use HasFactory<TrackFactory> */
    use HasFactory;

    protected $fillable = [
        'position',
        'title',
        'version',
        'duration_seconds',
        'isrc',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    /**
     * The title with its version, e.g. "Speak To Me (Edit)".
     */
    public function fullTitle(): string
    {
        return filled($this->version) ? "{$this->title} ({$this->version})" : $this->title;
    }

    /**
     * The duration in ISO 8601, e.g. "PT3M45S", for `<time datetime>` and JSON-LD.
     */
    public function isoDuration(): ?string
    {
        return $this->duration_seconds ? CarbonInterval::seconds($this->duration_seconds)->cascade()->spec() : null;
    }

    /**
     * The duration as "m:ss", e.g. "3:45".
     */
    protected function duration(): Attribute
    {
        return Attribute::get(fn (): ?string => Duration::format($this->duration_seconds));
    }
}
