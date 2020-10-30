<?php

declare(strict_types=1);

namespace Zeno\Router\Exception;

use Exception;
use Zeno\Router\Model\Route;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class ServiceUnavailableException extends Exception
{
    public function __construct(Route $route)
    {
        parent::__construct(
            sprintf('Service with id "%s" is currently unavailable, please try again later.', $route->id),
            503
        );
    }
}
