<?php

declare(strict_types=1);

namespace Zeno\Gateway\Protocol;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Zeno\Gateway\Action\Actions;
use Zeno\Gateway\Exception\ActionNotSupportedException;
use Zeno\Gateway\Protocol\Driver\Protocol;
use Zeno\Router\Model\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class ProtocolManager
{
    /**
     * @var Protocol[]
     */
    private array $protocols = [];

    public function __construct($protocols)
    {
        foreach ($protocols as $protocol) {
            $this->add($protocol);
        }
    }

    public function add(Protocol $protocol): void
    {
        $this->protocols[$protocol->name()] = $protocol;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->protocols);
    }

    /**
     * @param Actions $actions
     * @param Request $request
     *
     * @return ProtocolResponses[]|Collection
     */
    public function handle(Actions $actions, Request $request): Collection
    {
        $responses = $actions
            ->groupBy(fn(Action $action) => $action->service->driver)
            ->reduce(function (array $responses, Actions $batch) use ($request) {
                if (!$this->has($driver = $batch->first()->service->driver)) {
                    throw new ActionNotSupportedException($driver);
                }

                return array_merge($responses, [
                    $driver => $this->protocols[$driver]->handle($batch, $request),
                ]);
            }, []);

        return new Collection($responses);
    }
}
