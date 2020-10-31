<?php

declare(strict_types=1);

namespace App\Console\Commands\Db;

use App\Swoole\Coroutine;
use Illuminate\Database\Console\WipeCommand;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class DatabaseWipeConsole extends WipeCommand
{
    use Coroutine;

    public function handle()
    {
        return $this->coroutine(fn() => parent::handle());
    }
}
