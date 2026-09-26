<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage 1 of the redesign, step A (additive only): the structured catalogue
 * columns, the tracklist table and the artist/release pivot. Every new column
 * is nullable or has a default, so existing rows stay valid.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->date('released_at')->nullable();
            $table->string('format')->nullable();
            $table->string('genre')->nullable();
            $table->string('artist_display')->nullable();
            $table->string('spotify_url')->nullable();
            $table->string('smartlink_url')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->json('support')->nullable();
            $table->string('chart_position')->nullable();
            $table->string('chart_name')->nullable();
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->string('photo')->nullable();
            $table->string('role')->nullable();
            $table->string('origin')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('soundcloud_url')->nullable();
            $table->string('beatport_url')->nullable();
            $table->json('highlights')->nullable();
            $table->string('press_kit')->nullable();
        });

        Schema::table('playlists', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('spotify_url')->nullable();
            $table->string('cover_image')->nullable();
        });

        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('title');
            $table->string('version')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('isrc', 15)->nullable();
            $table->timestamps();

            $table->index(['release_id', 'position']);
        });

        Schema::create('artist_release', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('release_id')->constrained()->cascadeOnDelete();

            $table->unique(['artist_id', 'release_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_release');
        Schema::dropIfExists('tracks');

        Schema::table('playlists', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'spotify_url', 'cover_image']);
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'photo', 'role', 'origin', 'instagram_url', 'soundcloud_url',
                'beatport_url', 'highlights', 'press_kit',
            ]);
        });

        Schema::table('releases', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
        });

        Schema::table('releases', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'released_at', 'format', 'genre', 'artist_display', 'spotify_url',
                'smartlink_url', 'cover_image', 'is_featured', 'support', 'chart_position', 'chart_name',
            ]);
        });
    }
};
