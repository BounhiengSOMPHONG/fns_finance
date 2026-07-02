<?php

use App\Console\Commands\SyncExpenseAccountLinks;
use App\Console\Commands\SyncExpenseNames;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckUserActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        SyncExpenseNames::class,
        SyncExpenseAccountLinks::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO |
                Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->alias([
            'check.active' => CheckUserActive::class,
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
