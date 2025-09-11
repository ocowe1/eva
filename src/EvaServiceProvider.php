<?php
namespace Eva;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Foundation\Bootstrap\HandleExceptions;

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

        // Public tag agregada para facilitar publish all
        $this->publishes([
            __DIR__ . '/../config/eva.php' => config_path('eva.php'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/eva'),
        ], 'eva');

        // Registrar um reportable no Exception Handler que dispara o EVA
        // Apenas registra se a aplicação fornecer o ExceptionHandler contract
        if ($this->app->bound(ExceptionHandlerContract::class)) {
            $this->app->afterResolving(ExceptionHandlerContract::class, function ($handler, $app) {
                if (method_exists($handler, 'reportable')) {
                    $eva = $app->make('eva');
                    $handler->reportable(function (\Throwable $e) use ($eva) {
                        try {
                            $eva->capture($e);
                        } catch (\Throwable $ex) {
                            logger()->error('[EVA] erro ao capturar exceção: '.$ex->getMessage());
                        }
                    });
                }
            });
        }
    }
}
