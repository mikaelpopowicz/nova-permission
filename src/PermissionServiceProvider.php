<?php

namespace Mikaelpopowicz\NovaPermission;

use Laravel\Nova\Nova;
use Illuminate\Support\Collection;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use Mikaelpopowicz\NovaPermission\Resources\Role;
use Mikaelpopowicz\NovaPermission\Resources\Permission;
use Mikaelpopowicz\NovaPermission\Http\Middleware\Authorize;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Filesystem $filesystem, PermissionRegistrar $registrar)
    {
        $this->publishes([
            __DIR__.'/../config/nova-permission.php' => config_path('nova-permission.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/add_authorizable_to_permission_table.php.stub' => $this->getMigrationFileName($filesystem),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/nova-permission'),
        ], 'translations');


        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-permission');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'nova-permission');

        $this->app->booted(function () {
            $this->routes();
        });

        $this->addResources($registrar);

        Nova::serving(function (ServingNova $event) {

        });

        Nova::translations(
            collect(trans('nova-permission::permission-builder'))
                ->mapWithKeys(function ($value, $key) {
                    return ["permission-builder.{$key}" => $value];
                })
                ->toArray()
        );
    }

    protected function addResources(PermissionRegistrar $registrar)
    {
        Permission::$model = get_class($registrar->getPermissionClass());
        Role::$model = get_class($registrar->getRoleClass());

        Nova::resources([
            Resources\Permission::class,
            Resources\Role::class,
        ]);
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

        Route::middleware(['nova', Authorize::class])
            ->namespace('Mikaelpopowicz\NovaPermission\Http\Controllers')
            ->prefix('nova-vendor/nova-permission')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nova-permission.php', 'nova-permission');
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');
        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path.'*_add_authorizable_to_permission_table.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_add_authorizable_to_permission_table.php")
            ->first();
    }
}
