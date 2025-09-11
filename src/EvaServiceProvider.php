<?php
namespace Eva;

use Illuminate\Support\ServiceProvider;

class EvaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/eva.php', 'eva');
        $this->app->singleton('eva', function($app){
            return new Eva($app['config']->get('eva'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/eva.php' => config_path('eva.php'),
        ], 'eva-config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'eva');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/eva'),
        ], 'eva-views');
    }
}
