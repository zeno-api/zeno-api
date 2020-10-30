<?php

declare(strict_types=1);

namespace Zeno\Gateway\Exception;

use InvalidArgumentException;
use Zeno\Router\Model\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class ActionNotSupportedException extends InvalidArgumentException
{
    public function __construct(string $type)
    {
        parent::__construct(
            sprintf('Action with type "%s" is not supported', $type),
            500
        );
    }
}
