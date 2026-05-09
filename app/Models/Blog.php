<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'cover_image',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Auto-derive the slug from the title only when no slug exists yet.
     * Preserves existing public URLs across title edits.
     */
    protected function title(): Attribute
    {
        return Attribute::set(function (string $value, array $attributes): array {
            $attributes['title'] = $value;
            $attributes['slug'] = $attributes['slug'] ?? Str::slug($value);

            return $attributes;
        });
    }
}
