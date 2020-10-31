<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Swoole\Coroutine;
use Illuminate\Database\Console\Migrations\StatusCommand;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class MigrateStatusConsole extends StatusCommand
{
    use Coroutine;

    public function handle()
    {
        return $this->coroutine(fn() => parent::handle());
    }
}
