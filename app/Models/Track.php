<?php

namespace App\Models;

use App\Support\Duration;
use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * The duration as "m:ss", e.g. "3:45".
     */
    protected function duration(): Attribute
    {
        return Attribute::get(fn (): ?string => Duration::format($this->duration_seconds));
    }
}
