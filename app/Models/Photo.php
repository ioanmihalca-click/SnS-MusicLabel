<?php

namespace App\Models;

use App\Observers\PublicContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(PublicContentObserver::class)]
class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
    ];

    public function imageUrl(): string
    {
        return asset('storage/'.$this->image_path);
    }
}
