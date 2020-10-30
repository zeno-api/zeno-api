<?php

declare(strict_types=1);

namespace Zeno\Router\Handler;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Zeno\Gateway\Action\ActionManager;
use Zeno\Http\Presenter\Presenter;
use Zeno\Router\Exception\RouteNotFoundException;
use Zeno\Router\Exception\ServiceUnavailableException;
use Zeno\Router\Model\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RouteHandler
{
    private ActionManager $actionManager;
    private Presenter $presenter;

    public function __construct(ActionManager $actionManager, Presenter $presenter)
    {
        $this->actionManager = $actionManager;
        $this->presenter = $presenter;
    }

    public function __invoke(Request $request)
    {
        if (null === $routeId = $this->getRouteId($request)) {
            throw new RouteNotFoundException($routeId);
        }

        $route = $this->getRoute($routeId);

        if (!$route->available) {
            throw new ServiceUnavailableException($route);
        }

        $response = $this->actionManager->handle($route, $request);

        return $this->presenter->render($request, $response->getStatusCode(), $response->getData());
    }

    private function getRouteId(Request $request): ?string
    {
        [, $params] = $request->route();

        return $params['route_id'] ?? null;
    }

    private function getRoute(string $id): Route
    {
        return Cache::tags('zeno')->rememberForever(
            sprintf('route_%s', $id),
            fn() => Route::with('actions.service')->findOrFail($id)
        );
    }
}
