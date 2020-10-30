<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action;

use Illuminate\Http\Request;
use Zeno\Gateway\Action\Handler\ActionHandler;
use Zeno\Gateway\Exception\CannotHandleRouteException;
use Zeno\Router\Model\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class ActionManager
{
    /**
     * @var ActionHandler[]
     */
    private array $handlers = [];

    public function __construct($handlers)
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }
    }

    public function addHandler(ActionHandler $handler): void
    {
        $this->handlers[$handler->name()] = $handler;
    }

    public function hasHandler(string $name): bool
    {
        return array_key_exists($name, $this->handlers);
    }

    public function handle(Route $route, Request $request)
    {
        if (!$this->hasHandler($route->type)) {
            throw new CannotHandleRouteException($route);
        }

        return $this->handlers[$route->type]->handle($route, $request);
    }
}
