<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Zeno\Gateway\Action\ActionResponse;
use Zeno\Router\Model\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
trait Cacheable
{
    protected function hasCache(Route $route, Request $request): bool
    {
        return Cache::tags(['zeno'])->has($this->cacheKeyGenerator($route, $request));
    }

    protected function getCache(Route $route, Request $request)
    {
        return Cache::tags(['zeno'])->get($this->cacheKeyGenerator($route, $request));
    }

    protected function putCache(Route $route, Request $request, ActionResponse $response): void
    {
        Cache::tags(['zeno'])->put($this->cacheKeyGenerator($route, $request), $response, $route->freezeTtl);
    }

    protected function shouldBeCache(Route $route): bool
    {
        return true === $route->freeze;
    }

    protected function cacheKeyGenerator(Route $route, Request $request): string
    {
        return sha1($route->id . '|' . json_encode($request->all()));
    }
}
