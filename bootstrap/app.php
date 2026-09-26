<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\MarkdownResponse\Middleware\ProvideMarkdownResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // SecurityHeaders first, so it also covers the Markdown responses built after it.
        $middleware->web(append: [SecurityHeaders::class, ProvideMarkdownResponse::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
