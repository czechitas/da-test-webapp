<?php

declare(strict_types=1);

namespace CzechitasApp\Providers;

use Carbon\Carbon;
use CzechitasApp\Services\AresService;
use CzechitasApp\Services\BreadcrumbService;
use CzechitasApp\Services\FormatNameService;
use CzechitasApp\Services\Models\CategoryService;
use CzechitasApp\Services\Models\NewsService;
use CzechitasApp\Services\Models\OrderService;
use CzechitasApp\Services\Models\StudentService;
use CzechitasApp\Services\Models\TermService;
use CzechitasApp\Services\Models\UserService;
use CzechitasApp\Services\VariableSymbolService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Tamtamchik\NameCase\Formatter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<string, string>
     */
    public $bindings = [
        AresService::class          => AresService::class,
    ];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array<string, string>
     */
    public $singletons = [
        BreadcrumbService::class            => BreadcrumbService::class,
        CategoryService::class              => CategoryService::class,
        FormatNameService::class            => FormatNameService::class,
        NewsService::class                  => NewsService::class,
        OrderService::class                 => OrderService::class,
        StudentService::class               => StudentService::class,
        TermService::class                  => TermService::class,
        UserService::class                  => UserService::class,
        VariableSymbolService::class        => VariableSymbolService::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (\app()->getLocale() === 'cs') {
            Route::resourceVerbs([
                'create' => 'pridat',
                'edit' => 'upravit',
            ]);
        }
        Carbon::setToStringFormat('d.m.Y');

        Blade::withoutComponentTags();

        Formatter::setOptions([
            'irish' => false,
            'postnominal' => false,
            'lazy' => false,
        ]);

        Storage::extend('dropbox', static function ($app, $config) {
            if (!\class_exists(DropboxAdapter::class) || !\class_exists(Client::class)) {
                throw new \Exception('dropbox is not installed');
            }
            $adapter = new DropboxAdapter(new Client(
                $config['authorization_token'],
            ));

            $config['case_sensitive'] = false;

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config,
            );
        });

        Password::defaults(static fn () => Password::min(2)->letters()->mixedCase()->numbers());
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Keep original register method
    }
}
