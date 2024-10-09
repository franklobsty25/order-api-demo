<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use PHPUnit\Event\Code\Throwable;
use Symfony\Component\ErrorHandler\Error\FatalError;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Exception $e) {
            if ($request->is('api/*')) {

                return response()->json([
                    'message' => 'Something went wrong on the server. Please try again later!',
                ], 500);
            }
        });
    })->create();