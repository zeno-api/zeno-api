<?php

declare(strict_types=1);

namespace Zeno\Gateway\Protocol\Driver\Http;

use Borobudur\Component\Parameter\ParameterInterface;
use Psr\Http\Message\RequestInterface;
use Zeno\Router\Model\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
interface HttpRequestFactory
{
    public function createFromAction(Action $action, ParameterInterface $queryParams, ParameterInterface $params, ParameterInterface $files, array $paramsJar): RequestInterface;
}
