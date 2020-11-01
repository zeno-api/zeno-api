<?php

declare(strict_types=1);

namespace Zeno\Router\Handler;

use Illuminate\Http\Request;
use Zeno\Auth\Security\AuthManager;
use Zeno\Gateway\Action\ActionManager;
use Zeno\Http\Presenter\Presenter;
use Zeno\Router\Exception\RouteNotFoundException;
use Zeno\Router\Exception\ServiceUnavailableException;
use Zeno\Router\Repository\RouteRepository;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RouteHandler
{
    private ActionManager $actionManager;
    private Presenter $presenter;
    private RouteRepository $routeRepository;
    private AuthManager $authManager;

    public function __construct(ActionManager $actionManager, Presenter $presenter, RouteRepository $routeRepository, AuthManager $authManager)
    {
        $this->actionManager = $actionManager;
        $this->presenter = $presenter;
        $this->routeRepository = $routeRepository;
        $this->authManager = $authManager;
    }

    public function __invoke(Request $request)
    {
        if (null === $routeId = $this->getRouteId($request)) {
            throw new RouteNotFoundException($routeId);
        }

        $route = $this->routeRepository->find($routeId);
        $user = null;

        if (!$route->available) {
            throw new ServiceUnavailableException($route);
        }

        if (null !== $route->auth && $this->authManager->check()) {
            $user = $this->authManager->user();
        }

        $response = $this->actionManager->handle($route, $request, $user);

        return $this->presenter->render($request, $response->getStatusCode(), $response->getData());
    }

    private function getRouteId(Request $request): ?string
    {
        [, $params] = $request->route();

        return $params['route_id'] ?? null;
    }
}
