<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
 * Deletes the demos the label did not accept after 12 months
 * (App\Models\DemoSubmission::prunable()). Runs only where the scheduler's
 * cron (`php artisan schedule:run` every minute) is set up.
 */
Schedule::command('model:prune')->daily();
