<?php

declare(strict_types=1);

namespace Orchid\Builder;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Orchid\Platform\Dashboard;

/**
 * Class BuilderServiceProvider.
 */
class BuilderServiceProvider extends ServiceProvider
{

    /**
     * Boot the application events.
     *
     * @param Dashboard $dashboard
     *
     * @throws \Exception
     */
    public function boot(Dashboard $dashboard)
    {
        $dashboard
            ->addPublicDirectory('builder', __DIR__ . '/../public/')
            ->registerPermissions($this->registerPermissions());

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'builder');
        $this->loadJsonTranslationsFrom(realpath(__DIR__.'/../lang/'));

        View::composer('platform::layouts.app', function () use ($dashboard) {
            $dashboard->registerResource('scripts', orchid_mix('/js/builder.js', 'builder'));
        });

        View::composer('platform::container.systems.index', SystemMenuComposer::class);
    }

    /**
     * Register provider.
     */
    public function registerProviders()
    {
        foreach ($this->provides() as $provide) {
            $this->app->register($provide);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            RouteBuilderServiceProvider::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerProviders();
    }

    /**
     * @return array
     */
    protected function registerPermissions(): array
    {
        return [
            __('Systems') => [
                [
                    'slug'        => 'platform.builder',
                    'description' => __('Model Generator'),
                ],
            ],
        ];
    }
}
