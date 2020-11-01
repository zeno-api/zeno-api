<?php

declare(strict_types=1);

namespace Zeno\Http\Client\Driver;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Zeno\Gateway\Protocol\Driver\Http\HttpClient;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class GuzzleHttp implements HttpClient
{
    private Client $client;

    public function __construct(array $options = [])
    {
        $this->client = new Client(array_merge(
            [
                RequestOptions::TIMEOUT => 60,
            ],
            $options
        ));
    }

    public function send(RequestInterface $request, array $options = [])
    {
        return $this->client->sendAsync($request, $options);
    }
}
