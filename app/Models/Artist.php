<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'spotify_url',
        'description',
    ];

    protected $casts = [
        'order' => 'integer',
    ];
}