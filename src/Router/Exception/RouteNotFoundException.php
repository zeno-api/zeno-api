<?php

declare(strict_types=1);

namespace Zeno\Router\Exception;

use InvalidArgumentException;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RouteNotFoundException extends InvalidArgumentException
{
    public function __construct(string $routeId)
    {
        parent::__construct(sprintf('Route "%s" is not found', $routeId), 500);
    }
}
