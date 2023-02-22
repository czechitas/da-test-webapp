<?php

declare(strict_types=1);

namespace CzechitasApp\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

// phpcs:disable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
// phpcs:disable Squiz.PHP.CommentedOutCode
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<string>
     */
    protected $middleware = [
        \CzechitasApp\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \CzechitasApp\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \CzechitasApp\Http\Middleware\TrustProxies::class,
        \CzechitasApp\Http\Middleware\HttpsMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \CzechitasApp\Http\Middleware\ForceAppUrlMiddleware::class,
            \CzechitasApp\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \CzechitasApp\Http\Middleware\CheckDisabledUsers::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \CzechitasApp\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \CzechitasApp\Http\Middleware\ForceJsonResponse::class,
            'throttle:60,1',
            'bindings',
        ],

        'artisan' => [
            'throttle:20,1',
            \CzechitasApp\Http\Middleware\WebArtisanAuth::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, string>
     */
    protected $middlewareAliases = [
        'auth' => \CzechitasApp\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \CzechitasApp\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
