<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action\Handler;

use Illuminate\Http\Request;
use Zeno\Gateway\Action\ActionResponse;
use Zeno\Gateway\Action\Actions;
use Zeno\Gateway\Action\Helper\Cacheable;
use Zeno\Gateway\Protocol\ProtocolManager;
use Zeno\Gateway\Protocol\ProtocolResponses;
use Zeno\Router\Model\Route;
use Zeno\Router\Type\RouteType;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class SingleActionHandler implements ActionHandler
{
    use Cacheable;

    private ProtocolManager $protocolManager;

    public function __construct(ProtocolManager $protocolManager)
    {
        $this->protocolManager = $protocolManager;
    }

    public function handle(Route $route, Request $request, array $paramsJar): ActionResponse
    {
        if (null !== $data = $this->getCache($route, $request)) {
            return $data;
        }

        $responses = $this->protocolManager->handle(new Actions([$route->actions->first()]), $request, $paramsJar);
        /** @var ProtocolResponses $response */
        $response = $responses->first();
        $statusCodes = $response->codes();

        $output = new ActionResponse(
            $response->decodedResponses()->first(),
            reset($statusCodes)
        );

        if (!$response->hasFailedRequests() && $this->shouldBeCache($route)) {
            $this->putCache($route, $request, $output);
        }

        return $output;
    }

    public function name(): string
    {
        return RouteType::SINGLE;
    }
}
