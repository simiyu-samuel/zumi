<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(append: [
            \Illuminate\Http\Middleware\SetCacheHeaders::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \App\Http\Middleware\ApiGlobalMetadata::class,
        ]);
        
        $middleware->alias([
            'throttle.auth' => \Illuminate\Routing\Middleware\ThrottleRequests::class.':auth',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // DomainExceptions from the service layer return 422
        $exceptions->render(function (\DomainException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(
                    ['message' => $e->getMessage()],
                    \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        });

        // Socialite errors return 401
        $exceptions->render(function (\Laravel\Socialite\Two\InvalidStateException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(
                    ['message' => 'Social login failed. Please try again.'],
                    \Illuminate\Http\Response::HTTP_UNAUTHORIZED
                );
            }
        });
    })->create();
