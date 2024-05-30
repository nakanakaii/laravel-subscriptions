<?php

namespace Nakanakaii\LaravelSubscriptions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Nakanakaii\LaravelSubscriptions\Commands\CheckSubscription;

class SubscriptionsServiceProvider extends ServiceProvider
{
    /**
     * Initializes the application during the booting process.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../migrations/' => database_path('migrations'),
        ], 'subscriptions-migrations');
        $this->publishes([
            __DIR__.'/../config/subscriptions.php' => config_path('subscriptions.php'),
        ], 'subscriptions-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckSubscription::class,
            ]);
        }

        // Register the User Policy
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            if ($modelClass === 'App\\Models\\User') {
                return 'Nakanakaii\\LaravelSubscriptions\\Policies\\UserPolicy';
            }
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/subscriptions.php', 'subscriptions');
    }
}
