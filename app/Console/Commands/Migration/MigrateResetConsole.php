<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Swoole\Coroutine;
use Illuminate\Database\Console\Migrations\ResetCommand;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class MigrateResetConsole extends ResetCommand
{
    use Coroutine;

    public function handle()
    {
        return $this->coroutine(fn() => parent::handle());
    }
}
