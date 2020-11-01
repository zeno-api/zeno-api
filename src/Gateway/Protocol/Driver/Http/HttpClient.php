<?php

declare(strict_types=1);

namespace Zeno\Gateway\Protocol\Driver\Http;

use Psr\Http\Message\RequestInterface;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
interface HttpClient
{
    public function send(RequestInterface $request, array $options = []);
}
