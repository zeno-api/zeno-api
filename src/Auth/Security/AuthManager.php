<?php

declare(strict_types=1);

namespace Zeno\Auth\Security;

use Illuminate\Auth\AuthenticationException;
use Zeno\Auth\Dto\User;
use Zeno\Auth\Exception\UnsupportedGuardException;
use Zeno\Auth\Security\Guard\Guard;
use Zeno\Http\Request\RequestStack;
use Zeno\Router\Repository\RouteRepository;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class AuthManager
{
    /**
     * @var Guard[]
     */
    private array $guards = [];

    private ?User $user = null;
    private RequestStack $requestStack;
    private RouteRepository $routeRepository;

    public function __construct($guards, RequestStack $requestStack, RouteRepository $routeRepository)
    {
        foreach ($guards as $guard) {
            $this->addGuard($guard);
        }

        $this->requestStack = $requestStack;
        $this->routeRepository = $routeRepository;
    }

    public function addGuard(Guard $guard): void
    {
        $this->guards[$guard->name()] = $guard;
    }

    public function hasGuard(string $guard): bool
    {
        return array_key_exists($guard, $this->guards);
    }

    public function user(): ?User
    {
        if (null !== $this->user) {
            return $this->user;
        }

        $route = $this->routeRepository->find($this->getRouteId());

        if (!$this->hasGuard($guard = $route->auth->driver)) {
            throw new UnsupportedGuardException($guard);
        }

        return $this->user = $this->guards[$guard]->user($route->auth, $this->requestStack->request());
    }

    public function check(): bool
    {
        if (null === $this->user()) {
            throw new AuthenticationException();
        }

        return true;
    }

    private function getRouteId(): ?string
    {
        [, $params] = $this->requestStack->route();

        return $params['route_id'] ?? null;
    }
}
