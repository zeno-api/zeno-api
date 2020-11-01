<?php

declare(strict_types=1);

namespace Zeno\Gateway\Protocol\Driver\Http;

use Psr\Http\Message\RequestInterface;
use Zeno\Gateway\Action\RequestParams;
use Zeno\Router\Model\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
interface HttpRequestFactory
{
    public function createFromAction(Action $action, RequestParams $requestParams, array $paramsJar): RequestInterface;
}
