<?php

namespace App\Models;

use App\Observers\PublicContentObserver;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[ObservedBy(PublicContentObserver::class)]
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
        'hero_until',
        'hero_text',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'hero_until' => 'datetime',
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

    /**
     * Posts whose publication date has passed; drafts and scheduled posts stay hidden.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('published_at', '<=', now());
    }

    /**
     * Published posts announced in the homepage hero right now, newest published first.
     */
    #[Scope]
    protected function inHero(Builder $query): void
    {
        $query->published()
            ->where('hero_until', '>', now())
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    /**
     * Whether the homepage hero announces the post right now (see the inHero scope).
     */
    public function isInHero(): bool
    {
        return $this->isPublished() && $this->hero_until !== null && $this->hero_until->isFuture();
    }

    /**
     * The homepage hero line: the hero text, or else the title.
     */
    public function heroText(): string
    {
        return filled($this->hero_text) ? Str::squish($this->hero_text) : $this->title;
    }

    /**
     * The meta description, or the start of the post's text when none was written.
     */
    public function summary(): string
    {
        if (filled($this->meta_description)) {
            return Str::squish($this->meta_description);
        }

        return Str::limit(Str::squish(html_entity_decode(strip_tags((string) $this->content))), 160);
    }

    public function coverUrl(): ?string
    {
        return filled($this->cover_image) ? asset('storage/'.$this->cover_image) : null;
    }

    /**
     * When the public post last changed: a scheduled post changes when it goes live.
     */
    public function lastModifiedAt(): CarbonInterface
    {
        $updatedAt = $this->updated_at ?? now();

        if ($this->isPublished() && $this->published_at->gt($updatedAt)) {
            return $this->published_at;
        }

        return $updatedAt;
    }
}
