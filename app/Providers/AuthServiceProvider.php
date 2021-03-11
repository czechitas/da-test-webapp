<?php

declare(strict_types=1);

namespace CzechitasApp\Providers;

use CzechitasApp\Models\Category;
use CzechitasApp\Models\News;
use CzechitasApp\Models\Order;
use CzechitasApp\Models\Student;
use CzechitasApp\Models\Term;
use CzechitasApp\Models\User;
use CzechitasApp\Policies\CategoryPolicy;
use CzechitasApp\Policies\ExportPolicy;
use CzechitasApp\Policies\NewsPolicy;
use CzechitasApp\Policies\OrderPolicy;
use CzechitasApp\Policies\StudentPolicy;
use CzechitasApp\Policies\TermPolicy;
use CzechitasApp\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<string, string>
     */
    protected $policies = [
        Category::class             => CategoryPolicy::class,
        News::class                 => NewsPolicy::class,
        Order::class                => OrderPolicy::class,
        Student::class              => StudentPolicy::class,
        Term::class                 => TermPolicy::class,
        User::class                 => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('access-admin-routes', static function (User $user) {
            return $user->isRoleMaster();
        });

        Gate::resource('exports', ExportPolicy::class, [
            'list'          => 'list',
            'fullTerm'      => 'fullTerm',
            'overUnderPaid' => 'overUnderPaid',
        ]);
    }
}
