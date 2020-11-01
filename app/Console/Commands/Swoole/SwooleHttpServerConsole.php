<?php

declare(strict_types=1);

namespace App\Console\Commands\Swoole;

use Illuminate\Support\Arr;
use SwooleTW\Http\Commands\HttpServerCommand;
use SwooleTW\Http\Server\Facades\Server;
use SwooleTW\Http\Server\Manager;
use Upscale\Swoole\Blackfire\Profiler;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class SwooleHttpServerConsole extends HttpServerCommand
{
    protected function start()
    {
        if ($this->isRunning()) {
            $this->error('Failed! swoole_http_server process is already running.');

            return;
        }

        $host = Arr::get($this->config, 'server.host');
        $port = Arr::get($this->config, 'server.port');
        $hotReloadEnabled = Arr::get($this->config, 'hot_reload.enabled');
        $accessLogEnabled = Arr::get($this->config, 'server.access_log');

        $this->info('Starting swoole http server...');
        $this->info("Swoole http server started: <http://{$host}:{$port}>");
        if ($this->isDaemon()) {
            $this->info(
                '> (You can run this command to ensure the ' .
                'swoole_http_server process is running: ps aux|grep "swoole")'
            );
        }

        $manager = $this->laravel->make(Manager::class);
        $server = $this->laravel->make(Server::class);

        if ('prod' !== app()->environment()) {
            $profiler = new Profiler();
            $profiler->instrument($server);
        }

        if ($accessLogEnabled) {
            $this->registerAccessLog();
        }

        if ($hotReloadEnabled) {
            $manager->addProcess($this->getHotReloadProcess($server));
        }

        $manager->run();
    }
}
