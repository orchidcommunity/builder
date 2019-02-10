<?php

declare(strict_types=1);

namespace Orchid\Builder;

use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Orchid\Platform\Dashboard;

/**
 * Class RouteBootServiceProvider.
 */
class RouteBuilderServiceProvider extends RouteServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Orchid\Builder';

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        Route::domain((string)config('platform.domain'))
            ->prefix(Dashboard::prefix('/Builder'))
            ->middleware(config('platform.middleware.private'))
            ->namespace($this->namespace)
            ->group(function () {
                $this->screen('/{model?}', BootModelScreen::class)
                    ->name('platform.builder.index');
            });

        // Platform > System > Bulldozer
        Breadcrumbs::for('platform.builder.index', function ($trail) {
            $trail->parent('platform.systems.index');
            $trail->push(__('Model Builder'), route('platform.builder.index'));
        });
    }
}
