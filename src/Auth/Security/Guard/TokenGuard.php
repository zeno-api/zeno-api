<?php

declare(strict_types=1);

namespace Zeno\Auth\Security\Guard;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;
use Zeno\Auth\Dto\User;
use Zeno\Auth\Model\Auth;
use Zeno\Gateway\Protocol\Driver\Http\HttpClient;
use Zeno\Http\Request\RequestOptions;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class TokenGuard implements Guard
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function name(): string
    {
        return 'token';
    }

    public function user(Auth $auth, Request $request): ?User
    {
        if (null === $token = $this->getToken($request)) {
            return null;
        }

        if (true === $auth->cache && $user = Cache::tags(['zeno'])->get(sha1($token))) {
            return $user;
        }

        $bearer = $auth->option('bearer') ?? false;

        $request = new GuzzleRequest('GET', $auth->option('endpoint'), [
            'Accept'        => 'application/json',
            'Authorization' => $bearer ? sprintf('Bearer %s', $token) : $token,
        ]);

        /** @var ResponseInterface $response */
        $response = $this->httpClient->send($request, [RequestOptions::HTTP_ERRORS => false])->wait();

        if (200 !== $response->getStatusCode()) {
            return null;
        }

        $outputKey = $auth->options['output_key'] ?? null;
        $output = json_decode((string)$response->getBody(), true);
        $data = $outputKey ? $output[$outputKey] : $output;
        $user = new User($auth->option('identifier_key'), $data);

        if (true === $auth->cache) {
            Cache::tags(['zeno'])->put(sha1($token), $user, $auth->cache_ttl);
        }

        return $user;
    }

    private function getToken(Request $request): ?string
    {
        if (null !== $token = $request->query('_token')) {
            return $token;
        }

        if (null !== $token = $request->bearerToken()) {
            return $token;
        }

        return null;
    }
}
