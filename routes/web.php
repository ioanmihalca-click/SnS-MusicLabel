<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IndexNowKeyController;
use App\Http\Controllers\LlmsTxtController;
use App\Http\Controllers\PlaylistsController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Livewire\BlogIndex;
use App\Livewire\BlogShow;
use App\Livewire\ReleaseCatalogue;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/index', HomeController::class)->name('home.index');

Route::get('/releases', ReleaseCatalogue::class)->name('releases.index');
Route::get('/releases/{release:slug}', [ReleaseController::class, 'show'])->name('releases.show');

Route::get('/artists', [ArtistController::class, 'index'])->name('artists.index');
Route::get('/artists/{artist:slug}', [ArtistController::class, 'show'])->name('artists.show');

Route::get('/playlists', PlaylistsController::class)->name('playlists.index');
Route::get('/about', AboutController::class)->name('about');

Route::get('/blog', BlogIndex::class)->name('blog.index');
Route::get('/blog/{slug}', BlogShow::class)->name('blog.show');

Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/llms.txt', LlmsTxtController::class)->name('llms-txt');
Route::get('/{key}.txt', IndexNowKeyController::class)
    ->where('key', '[a-f0-9]{32}')
    ->name('indexnow.key');
