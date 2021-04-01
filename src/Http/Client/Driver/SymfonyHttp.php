<?php

declare(strict_types=1);

namespace Zeno\Http\Client\Driver;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttplugClient;
use Zeno\Gateway\Protocol\Driver\Http\HttpClient as HttpClientContract;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class SymfonyHttp implements HttpClientContract
{
    private HttplugClient $client;

    public function __construct(array $options = [])
    {
        $this->client = new HttplugClient(
            HttpClient::create(array_merge([
                'max_duration' => 30,
            ], $options))
        );
    }

    public function send(RequestInterface $request, array $options = [])
    {
        return $this->client->sendAsyncRequest($request);
    }
}
