<?php

use App\Console\Commands\Subscriptions\NotifyCompletedTrials;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        then: function () {
            Route::middleware('api')
                ->prefix('webhooks')
                ->name('webhooks.')
                ->group(base_path('routes/webhooks.php'));
        }
    )
    ->withEvents(discover: [
        __DIR__ . '/../app/Listeners/*',
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command(NotifyCompletedTrials::class)->everyMinute();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
