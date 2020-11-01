<?php

declare(strict_types=1);

namespace Zeno\Auth\Exception;

use InvalidArgumentException;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class UnsupportedGuardException extends InvalidArgumentException
{
    public function __construct(string $guard)
    {
        parent::__construct(sprintf('Unsupported guard "%s"', $guard), 500);
    }
}
