<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Swoole\Coroutine;
use Illuminate\Database\Console\Migrations\FreshCommand;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class MigrateFreshConsole extends FreshCommand
{
    use Coroutine;

    public function handle()
    {
        return $this->coroutine(fn() => parent::handle());
    }
}
