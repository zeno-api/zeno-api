<?php

declare(strict_types=1);

namespace App\Console\Commands\Config;

use Illuminate\Console\Command;
use Laravel\Lumen\Application;
use ReflectionObject;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class CacheConfigConsole extends Command
{
    protected $signature = 'config:cache';

    public function handle(Application $app): void
    {
        $cachePath = $app->basePath('bootstrap/cache/config.php');
        $index = [
            'loaded' => $this->getLoadedConfigurations($app),
            'index'  => $this->index($app->get('config')->all()),
        ];
        $export = var_export($index, true);

        $output = <<<EOT
        <?php
        return $export;
        EOT;

        file_put_contents($cachePath, $output);
    }

    private function index(array $config, array $index = [], $prefix = '')
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $index = $this->index($value, $index, $key);
            }

            $index[$prefix ? $prefix.'.'.$key : $key] = $value;
        }

        return $index;
    }

    private function getLoadedConfigurations(Application $app): array
    {
        $reflection = new ReflectionObject($app);
        $prop = $reflection->getProperty('loadedConfigurations');
        $prop->setAccessible(true);

        return $prop->getValue($app);
    }
}
