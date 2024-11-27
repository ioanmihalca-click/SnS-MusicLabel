<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = ['spotify_embed_url', 'order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}