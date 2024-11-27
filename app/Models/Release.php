<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'spotify_embed_code',
    ];

    protected $attributes = [
        'spotify_embed_code' => '',
    ];
}