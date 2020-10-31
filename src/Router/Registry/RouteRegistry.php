<?php

declare(strict_types=1);

namespace Zeno\Router\Registry;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;
use Zeno\Router\Model\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class RouteRegistry
{
    private static bool $booted = false;

    public function boot(Application $app): void
    {
        if (config('app.enable_static_route') && self::$booted) {
            return;
        }

        self::$booted = true;

        if (config('app.enable_cache') && !empty($routes = Cache::tags(['zeno'])->get('routes'))) {
            $this->loadRouteFromCaches($app, $routes);

            return;
        }

        $this->getRoutes()->each(
            function (Route $route) use ($app) {
                foreach ($route->methods as $method) {
                    $app->router->{strtolower($method)}($route->path, [
                        'uses'     => config('app.caller.handler'),
                        'route_id' => $route->id,
                    ]);
                }
            }
        );

        if (config('app.enable_cache')) {
            $this->cacheRoutes($app);
        }
    }

    private function loadRouteFromCaches(Application $app, array $routes): void
    {
        $loadRoutes = Closure::bind(function (Router $router, array $routes) {
            $router->routes = $routes['routes'];
            $router->namedRoutes = $routes['named_routes'];
            $router->groupStack = $routes['group_stack'];
        }, null, $app->router);

        $loadRoutes($app->router, $routes);
    }

    private function cacheRoutes(Application $app): void
    {
        $getRoutes = Closure::bind(function (Router $router) {
            return [
                'routes'       => $router->routes,
                'named_routes' => $router->namedRoutes,
                'group_stack'  => $router->groupStack,
            ];
        }, null, $app->router);

        Cache::tags(['zeno'])->put('routes', $getRoutes($app->router));
    }

    private function getRoutes(): Collection
    {
        return Route::published()->get();
    }
}
