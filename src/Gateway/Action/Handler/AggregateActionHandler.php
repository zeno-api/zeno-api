<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action\Handler;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Zeno\Gateway\Action\ActionResponse;
use Zeno\Gateway\Action\Actions;
use Zeno\Gateway\Action\Helper\Cacheable;
use Zeno\Gateway\Action\RequestParams;
use Zeno\Gateway\Protocol\ProtocolManager;
use Zeno\Gateway\Protocol\ProtocolResponses;
use Zeno\Router\Model\Action;
use Zeno\Router\Model\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class AggregateActionHandler implements ActionHandler
{
    use Cacheable;

    private ProtocolManager $protocolManager;

    public function __construct(ProtocolManager $protocolManager)
    {
        $this->protocolManager = $protocolManager;
    }

    public function handle(Route $route, Request $request, RequestParams $requestParams, array $paramsJar): ActionResponse
    {
        if (null !== $data = $this->getCache($route, $request)) {
            return $data;
        }

        $failures = 0;
        $responses = $this->getActions($route)->reduce(function (array $cary, Actions $batch) use ($request, &$paramsJar, &$failures, $requestParams) {
            $output = $this->protocolManager->handle($batch, $request, $requestParams, $paramsJar);

            return array_merge($cary, $output->reduce(function (array $batchCary, ProtocolResponses $protocolResponses) use (&$paramsJar, &$failures) {
                $data = $protocolResponses->decodedResponses()->all();
                $paramsJar = array_merge($paramsJar, $data);
                $failures += $protocolResponses->totalFailures();

                return array_merge($batchCary, $data);
            }, []));
        }, []);

        $output = new ActionResponse(
            $responses,
            Response::HTTP_OK
        );

        if (0 === $failures && $this->shouldBeCache($route)) {
            $this->putCache($route, $request, $output);
        }

        return $output;
    }

    public function name(): string
    {
        return 'aggregate';
    }

    private function getActions(Route $route): Collection
    {
        return $route->actions
            ->groupBy(fn(Action $action) => $action->sequence)
            ->sortBy(fn($batch, $key) => (int) $key)
            ->map(function (Collection $batch) {
                return new Actions($batch->all());
            });
    }
}
