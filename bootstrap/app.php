<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        //web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->remove([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\View\Middleware\ShareErrorsFromSession::class, // Já não estaria se StartSession for removido
            \App\Http\Middleware\VerifyCsrfToken::class, // CSRF não é usado em APIs stateless
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configura para que TODAS as respostas de exceção sejam JSON
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return true; // Força JSON para todas as respostas de erro
        });
    })->create();
