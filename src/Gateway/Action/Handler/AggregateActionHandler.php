<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action\Handler;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Zeno\Gateway\Action\ActionResponse;
use Zeno\Gateway\Action\Actions;
use Zeno\Gateway\Protocol\ProtocolManager;
use Zeno\Gateway\Protocol\ProtocolResponses;
use Zeno\Router\Model\Action;
use Zeno\Router\Model\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class AggregateActionHandler implements ActionHandler
{
    private ProtocolManager $protocolManager;

    public function __construct(ProtocolManager $protocolManager)
    {
        $this->protocolManager = $protocolManager;
    }

    public function handle(Route $route, Request $request): ActionResponse
    {
        $responses = $this->getActions($route)->reduce(function (array $cary, Actions $batch) use ($request) {
            $output = $this->protocolManager->handle($batch, $request);

            return array_merge($cary, $output->reduce(function (array $batchCary, ProtocolResponses $protocolResponses) {
                return array_merge($batchCary, $protocolResponses->decodedResponses()->all());
            }, []));
        }, []);

        return new ActionResponse(
            $responses,
            Response::HTTP_OK
        );
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
