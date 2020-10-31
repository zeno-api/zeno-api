<?php

declare(strict_types=1);

namespace App\Console\Commands\Db;

use App\Swoole\Coroutine;
use Illuminate\Database\Console\Seeds\SeedCommand;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class DatabaseSeedConsole extends SeedCommand
{
    use Coroutine;

    public function handle()
    {
        return $this->coroutine(fn() => parent::handle());
    }
}
