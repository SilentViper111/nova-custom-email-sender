<?php

namespace Dniccum\CustomEmailSender;

use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Dniccum\CustomEmailSender\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    private $config = 'novaemailsender';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'custom-email-sender');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        $this->app->booted(function () {
            $this->routes();
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/'.$this->config.'.php' => base_path('config/'.$this->config.'.php'),
            ], 'config');
        }

        $this->publishes([
            __DIR__.'/../resources/views/email.blade.php' => resource_path('views/vendor/custom-email-sender/email.blade.php'),
        ], 'views');
        
        Nova::serving(function (ServingNova $event) {
            //
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', 'nova.auth', Authorize::class], 'custom-email-sender')
            ->group(__DIR__.'/../routes/inertia.php');

        Route::middleware(['nova', 'nova.auth', Authorize::class])
                ->prefix('nova-vendor/custom-email-sender')
                ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/'.$this->config.'.php', $this->config);
    }
}
