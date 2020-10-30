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
    public function boot(Application $app): void
    {
        if (Cache::tags('zeno')->has('routes')) {
            $this->loadRouteFromCaches($app);

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

        $this->cacheRoutes($app);
    }

    private function loadRouteFromCaches(Application $app): void
    {
        $loadRoutes = Closure::bind(function (Router $router, array $routes) {
            $router->routes = array_merge($router->routes, $routes['routes']);
            $router->namedRoutes = array_merge($router->namedRoutes, $routes['named_routes']);
            $router->groupStack = array_merge($router->groupStack, $routes['group_stack']);
        }, null, $app->router);

        $loadRoutes($app->router, Cache::tags('zeno')->get('routes', []));
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

        Cache::tags('zeno')->put('routes', $getRoutes($app->router));
    }

    private function getRoutes(): Collection
    {
        return Route::published()->get();
    }
}
