<?php

declare(strict_types=1);

namespace Zeno\Management\Command;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Zeno\Management\Model\Client;
use Zeno\Router\Model\Action;
use Zeno\Router\Model\Route;
use Zeno\Shared\Helper\ValidationHelper;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Synchronize
{
    use ValidationHelper;

    public function __invoke(Request $request, AuthManager $authManager): JsonResponse
    {
        $this->validateRequest($request);

        DB::transaction(function () use ($request, $authManager) {
            /** @var Client $client */
            $client = $authManager->guard('signature')->user();

            $this->clearMangedRoutes($client);
            $this->registerRoutes($client, $request->get('routes'));
        });

        Cache::tags(['zeno'])->flush();
        Artisan::call('swoole:http', ['action' => 'reload']);

        return new JsonResponse(['success' => true], 200);
    }

    private function registerRoutes(Client $client, array $routes): void
    {
        foreach ($routes as $route) {
            $routeModel = Route::create(array_merge(
                Arr::except($route, ['actions']),
                [
                    'managed_by' => $client->id,
                    'available'  => true,
                    'published'  => true,
                ]
            ));

            $this->registerActions($routeModel, $route['actions']);
        }
    }

    private function registerActions(Route $route, array $actions): void
    {
        foreach ($actions as $action) {
            Action::create(array_merge($action, [
                'route_id' => $route->id,
            ]));
        }
    }

    private function clearMangedRoutes(Client $client): void
    {
        Route::where('managed_by', $client->id)->delete();
    }

    private function validateRequest(Request $request): void
    {
        $this->validate($request, [
            'routes'                            => 'required|array',
            'routes.*.path'                     => 'required|string',
            'routes.*.methods'                  => 'required|array',
            'routes.*.type'                     => 'required|in:single,aggregate',
            'routes.*.freeze'                   => 'required|bool',
            'routes.*.freeze_ttl'               => 'nullable|numeric',
            'routes.*.auth_id'                  => 'nullable|uuid',
            'routes.*.forward_headers'          => 'nullable|array',
            'routes.*.actions'                  => 'required|array',
            'routes.*.actions.*.service_id'     => 'required|uuid|exists:services,id',
            'routes.*.actions.*.sequence'       => 'nullable|numeric',
            'routes.*.actions.*.response_key'   => 'nullable',
            'routes.*.actions.*.destination'    => 'required',
            'routes.*.actions.*.options.method' => 'required|in:get,put,post,patch,delete',
        ]);
    }
}
