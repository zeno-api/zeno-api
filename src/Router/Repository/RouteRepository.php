<?php

declare(strict_types=1);

namespace Zeno\Router\Repository;

use Illuminate\Support\Facades\Cache;
use Zeno\Router\Model\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RouteRepository
{
    /**
     * @var Route[]
     */
    private array $caches = [];

    public function find(string $id): Route
    {
        if (null !== $route = $this->caches[$id] ?? null) {
            return  $route;
        }

        $cacheKey = sprintf('route_%s', $id);

        if (config('app.enable_cache') && null !== $route = Cache::tags(['zeno'])->get($cacheKey)) {
            return $route;
        }

        $route = Route::with('actions.service', 'auth')->findOrFail($id);

        if (config('app.enable_cache')) {
            Cache::tags(['zeno'])->put($cacheKey, $route);
        }

        return $this->caches[$id] = $route;
    }
}
