<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Swoole\Coroutine;
use Illuminate\Database\Console\Migrations\RollbackCommand;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class MigrateRollbackConsole extends RollbackCommand
{
    use Coroutine;

    public function handle()
    {
        return $this->coroutine(fn() => parent::handle());
    }
}
