<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\RedirectIfNotUser;
use App\Http\Middleware\RedirectIfUser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require_once __DIR__.'/../app/Support/polyfills.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            Route::namespace('App\Http\Controllers')->group(function () {
                Route::prefix('api')
                    ->middleware(['api'])
                    ->group(base_path('routes/api.php'));

                Route::middleware(['web'])
                    ->prefix('admin')
                    ->name('admin.')
                    ->group(base_path('routes/admin.php'));

                Route::middleware(['web'])
                    ->prefix('agence')
                    ->name('agence.')
                    ->group(base_path('routes/user.php'));

                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\Cors::class);

        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\EnsureTokenIsValid::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->group('admin', [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\EnsureTokenIsValid::class,
            \App\Http\Middleware\RedirectIfNotAdmin::class,
        ]);

        $middleware->group('user', [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\EnsureTokenIsValid::class,
            \App\Http\Middleware\RedirectIfNotUser::class,
        ]);

        $middleware->alias([
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'admin' => RedirectIfNotAdmin::class,
            'admin.guest' => RedirectIfAdmin::class,
            'user' => RedirectIfNotUser::class,
            'user.guest' => RedirectIfUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $resolveErrorContext = function (Request $request): array {
            $path = ltrim($request->path(), '/');
            $area = str_starts_with($path, 'admin')
                ? 'admin'
                : (str_starts_with($path, 'agence') ? 'agence' : 'public');

            return [
                'area' => $area,
                'dashboardUrl' => $area === 'admin'
                    ? route('admin.dashboard')
                    : ($area === 'agence' ? route('agence.dashboard') : url('/')),
                'loginUrl' => $area === 'admin'
                    ? route('admin.login')
                    : ($area === 'agence' ? route('agence.login') : url('/')),
                'homeUrl' => url('/'),
                'returnUrl' => $request->fullUrl(),
            ];
        };

        $exceptions->render(function (\Throwable $e, Request $request) use ($resolveErrorContext) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            if ($e instanceof AuthenticationException) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if (! in_array($status, [401, 403, 404, 419, 500, 503], true)) {
                return null;
            }

            $context = $resolveErrorContext($request);

            $statusMeta = [
                401 => [
                    'title' => 'Non authentifié',
                    'message' => 'Vous devez être connecté pour accéder à cette page.',
                    'icon' => 'shield',
                    'primaryLabel' => 'Se connecter',
                ],
                403 => [
                    'title' => 'Accès interdit',
                    'message' => "Vous n'avez pas les permissions nécessaires pour accéder à cette page.",
                    'icon' => 'ban',
                    'primaryLabel' => 'Retour au dashboard',
                ],
                404 => [
                    'title' => 'Page introuvable',
                    'message' => 'La ressource demandée est introuvable.',
                    'icon' => 'search',
                    'primaryLabel' => null,
                ],
                419 => [
                    'title' => 'Session expirée',
                    'message' => 'Votre session a expiré. Veuillez rafraîchir la page et réessayer.',
                    'icon' => 'clock',
                    'primaryLabel' => 'Recharger la page',
                ],
                500 => [
                    'title' => 'Erreur serveur',
                    'message' => 'Une erreur inattendue est survenue. Notre équipe technique a été notifiée.',
                    'icon' => 'server',
                    'primaryLabel' => 'Retour au dashboard',
                ],
                503 => [
                    'title' => 'Maintenance en cours',
                    'message' => 'Le système est temporairement indisponible pour maintenance.',
                    'icon' => 'server',
                    'primaryLabel' => null,
                ],
            ][$status];

            $primaryHref = match ($status) {
                401 => $context['loginUrl'],
                419 => $context['returnUrl'],
                403, 500 => $context['dashboardUrl'],
                default => $context['homeUrl'],
            };

            return Inertia::render('Errors/Index', [
                'status' => $status,
                'title' => $statusMeta['title'],
                'message' => $statusMeta['message'],
                'icon' => $statusMeta['icon'],
                'primaryAction' => $statusMeta['primaryLabel'] ? [
                    'label' => $statusMeta['primaryLabel'],
                    'href' => $primaryHref,
                ] : null,
            ])->toResponse($request)->setStatusCode($status);
        });
    })
    ->create();
