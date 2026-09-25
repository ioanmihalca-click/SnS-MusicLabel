<?php

namespace App\Models;

use App\Support\Slug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'order',
        'spotify_url',
        'description',
        'photo',
        'role',
        'origin',
        'instagram_url',
        'soundcloud_url',
        'beatport_url',
        'highlights',
        'press_kit',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'highlights' => 'array',
        ];
    }

    /**
     * Generate the slug only while it is empty, so renaming the artist keeps the public URL.
     */
    protected static function booted(): void
    {
        static::saving(function (Artist $artist): void {
            if (filled($artist->slug)) {
                return;
            }

            $artist->slug = Slug::unique(
                (string) $artist->name,
                fn (string $slug): bool => static::query()
                    ->where('slug', $slug)
                    ->when($artist->exists, fn (Builder $query) => $query->whereKeyNot($artist->getKey()))
                    ->exists(),
            );
        });
    }

    public function releases(): BelongsToMany
    {
        return $this->belongsToMany(Release::class);
    }
}
