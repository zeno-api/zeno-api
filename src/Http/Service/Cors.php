<?php

declare(strict_types=1);

namespace Zeno\Http\Service;

use Asm89\Stack\CorsService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Cors
{
    private Container $container;
    private CorsService $cors;

    public function __construct(Container $container, CorsService $cors)
    {
        $this->container = $container;
        $this->cors = $cors;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed
     */
    public function handle(Request $request, Response $response)
    {
        if (!$this->shouldRun($request)) {
            return $response;
        }

        if ($this->cors->isPreflightRequest($request)) {
            $response = $this->cors->handlePreflightRequest($request);

            $this->cors->varyHeader($response, 'Access-Control-Request-Method');

            return $response;
        }

        if ('OPTIONS' === $request->getMethod()) {
            $this->cors->varyHeader($response, 'Access-Control-Request-Method');
        }
    }

    protected function addHeaders(Request $request, Response $response): Response
    {
        if (! $response->headers->has('Access-Control-Allow-Origin')) {
            // Add the CORS headers to the Response
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Determine if the request has a URI that should pass through the CORS flow.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function shouldRun(Request $request): bool
    {
        return $this->isMatchingPath($request);
    }

    /**
     * The the path from the config, to see if the CORS Service should run
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isMatchingPath(Request $request): bool
    {
        // Get the paths from the config or the middleware
        $paths = $this->container['config']->get('cors.paths', []);

        foreach ($paths as $path) {
            if ($path !== '/') {
                $path = trim($path, '/');
            }

            if ($request->fullUrlIs($path) || $request->is($path)) {
                return true;
            }
        }

        return false;
    }
}
