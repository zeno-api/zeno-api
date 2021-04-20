<?php

declare(strict_types=1);

namespace Zeno\Gateway\Protocol\Driver\Http;

use Borobudur\Component\Parameter\ImmutableParameter;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Zeno\Gateway\Action\Actions;
use Zeno\Gateway\Action\RequestParams;
use Zeno\Gateway\Protocol\ProtocolResponses;
use Zeno\Gateway\Protocol\Driver\Protocol;
use Zeno\Router\Model\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class HttpProtocol implements Protocol
{
    private HttpClient $httpClient;
    private HttpRequestFactory $requestFactory;

    public function __construct(HttpClient $httpClient, HttpRequestFactory $requestFactory)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }


    public function name(): string
    {
        return 'http';
    }

    public function handle(Actions $actions, Request $request, RequestParams $requestParams, array $paramsJar): ProtocolResponses
    {
        $promises = $actions->reduce(function (array $promises, Action $action) use ($request, $paramsJar, $requestParams) {
            return array_merge($promises, [
                $action->response_key => $this->httpClient->send(
                    $this->requestFactory->createFromAction($action, $requestParams, $paramsJar)
                ),
            ]);
        }, []);

        return $this->parseResponse(new ProtocolResponses(), new Collection(Utils::settle($promises)->wait()));
    }

    private function parseResponse(ProtocolResponses $actionResponse, Collection $responses): ProtocolResponses
    {
        $responses
            ->filter(fn(array $response) => 'fulfilled' === $response['state'])
            ->each(function (array $response, ?string $key) use ($actionResponse) {
                /** @var ResponseInterface $httpResponse */
                $httpResponse = $response['value'];

                $actionResponse->addSuccessResponse(
                    $key,
                    (string) $httpResponse->getBody(),
                    $httpResponse->getStatusCode(),
                    $httpResponse->getHeader('Content-Type')
                );
            });

        $responses
            ->filter(fn(array $response) => 'fulfilled' !== $response['state'])
            ->each(function (array $response, ?string $key) use ($actionResponse) {
                /** @var RequestException $httpException */
                $httpException = $response['reason'];
                $httpResponse = $httpException->getResponse();

                $actionResponse->addFailureResponse(
                    $key,
                    (string) $httpResponse->getBody(),
                    $httpResponse->getStatusCode(),
                    $httpResponse->getHeader('Content-Type')
                );
            });

        return $actionResponse;
    }
}
