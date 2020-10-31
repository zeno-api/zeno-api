<?php

declare(strict_types=1);

namespace Zeno\Router\Provider;

use Illuminate\Support\ServiceProvider;
use Zeno\Router\Registry\RouteRegistry;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registry()->boot($this->app);
    }

    public function register(): void
    {
        $this->app->singleton(RouteRegistry::class);
    }

    private function registry(): RouteRegistry
    {
        return $this->app->get(RouteRegistry::class);
    }
}
