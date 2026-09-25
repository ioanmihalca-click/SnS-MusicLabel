<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\IndexNowKeyController;
use App\Http\Controllers\LlmsTxtController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Livewire\BlogIndex;
use App\Livewire\BlogShow;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/index', HomeController::class)->name('home.index');

Route::get('/blog', BlogIndex::class)->name('blog.index');
Route::get('/blog/{slug}', BlogShow::class)->name('blog.show');

Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/llms.txt', LlmsTxtController::class)->name('llms-txt');
Route::get('/{key}.txt', IndexNowKeyController::class)
    ->where('key', '[a-f0-9]{32}')
    ->name('indexnow.key');
