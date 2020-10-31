<?php

declare(strict_types=1);

namespace App\Swoole;

use Swoole\Coroutine as SwooleCoroutine;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
trait Coroutine
{
    protected function coroutine(callable $callable)
    {
        if (-1 === SwooleCoroutine::getuid()) {
            return go($callable);
        }

        return $callable();
    }
}
