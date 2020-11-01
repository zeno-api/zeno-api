<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action;

use Borobudur\Component\Parameter\ImmutableParameter;
use Borobudur\Component\Parameter\ParameterInterface;
use Illuminate\Http\Request;
use Zeno\Auth\Dto\User;
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

    public function handle(Route $route, Request $request, ?User $user)
    {
        if (!$this->hasHandler($route->type)) {
            throw new CannotHandleRouteException($route);
        }

        $paramJars = array_merge($request->input(), $request->route()[2] ?? []);

        if (null !== $user) {
            $paramJars['_auth'] = $user->toArray();
        }

        return $this->handlers[$route->type]->handle(
            $route,
            $request,
            $this->createRequestParams($user, $route, $request),
            $paramJars
        );
    }

    private function createRequestParams(?User $user, Route $route, Request $request): RequestParams
    {
        return new RequestParams(
            $user,
            $this->getHeaders($route, $request),
            new ImmutableParameter($request->request->all()),
            new ImmutableParameter($request->query->all()),
            new ImmutableParameter($request->files->all()),
        );
    }

    private function getHeaders(Route $route, Request $request): ParameterInterface
    {
        $headers = [];

        if (!empty($forwardHeaders = $route->forward_headers)) {
            $headers = array_filter(
                $request->headers->all(),
                fn(string $key) => in_array($key, array_map('strtolower', $forwardHeaders)),
                ARRAY_FILTER_USE_KEY
            );
        }

        return new ImmutableParameter($headers);
    }
}
