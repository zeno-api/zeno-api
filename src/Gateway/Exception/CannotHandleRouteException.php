<?php

declare(strict_types=1);

namespace Zeno\Gateway\Exception;

use InvalidArgumentException;
use Zeno\Router\Model\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class CannotHandleRouteException extends InvalidArgumentException
{
    public function __construct(Route $route)
    {
        parent::__construct(sprintf('Cannot handle route with path "%s"', $route->path), 500);
    }
}
