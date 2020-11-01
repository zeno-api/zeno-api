<?php

declare(strict_types=1);

namespace Zeno\Auth\Provider;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Zeno\Auth\Security\AuthManager;
use Zeno\Auth\Security\Guard\TokenGuard;
use Zeno\Http\Request\RequestStack;
use Zeno\Router\Repository\RouteRepository;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerGuards();

        $this->app->singleton(AuthManager::class, fn (Application $app) => new AuthManager(
            $app->tagged('zeno_auth_guards'),
            $app->get(RequestStack::class),
            $app->get(RouteRepository::class)
        ));
    }

    private function registerGuards(): void
    {
        $this->app->singleton(TokenGuard::class);

        $this->app->tag([TokenGuard::class], 'zeno_auth_guards');
    }
}
