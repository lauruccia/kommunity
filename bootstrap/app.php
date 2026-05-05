<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->alias([
            'onboarding' => \App\Http\Middleware\EnsureOnboardingComplete::class,
            // Spatie/laravel-permission middleware (registrazione manuale, Laravel 11+)
            'role'             => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'       => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Routing\Exceptions\InvalidSignatureException $e, \Illuminate\Http\Request $request) {
            if (! $request->routeIs('verification.verify')) {
                return null;
            }

            $message = 'Il link di attivazione non è valido o è scaduto. Accedi e richiedi un nuovo link di verifica.';

            if ($request->user()) {
                return redirect()->route('verification.notice')->with('warning', $message);
            }

            return redirect()->route('login')->with('warning', $message);
        });

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            $message = 'La sessione è scaduta o hai inviato un form aperto da troppo tempo. Ricarica la pagina e accedi di nuovo.';

            if ($request->is('admin/*') || $request->is('admin')) {
                return redirect('/admin/login')->with('warning', $message);
            }

            return redirect()->route('login')->with('warning', $message);
        });
    })->create();
