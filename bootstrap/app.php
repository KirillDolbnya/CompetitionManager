<?php

use App\Jobs\SendCompetitionNotifications;
use App\Models\Competition;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(function () {
           Competition::where('cleanup_at', '<=', now())
               ->get()
               ->each(function (Competition $competition) {
                   if ($competition->file_path){
                        Storage::disk('public')->delete($competition->file_path);
                   }

                   if ($competition->image_path){
                       Storage::disk('public')->delete($competition->image_path);
                   }

                   $competition->delete();
               });
        })->daily();

        $schedule->call(function () {
            Competition::whereDate('end_at', today())
                ->get()
                ->each(function (Competition $competition) {
                    SendCompetitionNotifications::dispatch($competition);
                });
        })->dailyAt('21:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
