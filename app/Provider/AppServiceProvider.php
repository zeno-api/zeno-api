<?php

declare(strict_types=1);

namespace App\Provider;

use App\Console\Commands\Db\DatabaseSeedConsole;
use App\Console\Commands\Db\DatabaseWipeConsole;
use App\Console\Commands\Migration\MigrateConsole;
use App\Console\Commands\Migration\MigrateFreshConsole;
use App\Console\Commands\Migration\MigrateRefreshConsole;
use App\Console\Commands\Migration\MigrateResetConsole;
use App\Console\Commands\Migration\MigrateRollbackConsole;
use App\Console\Commands\Migration\MigrateStatusConsole;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class AppServiceProvider extends ServiceProvider
{
    private array $commands = [
        'Migrate',
        'MigrateFresh',
        'MigrateRefresh',
        'MigrateReset',
        'MigrateRollback',
        'MigrateStatus',
        'DbSeed',
        'DbWipe',
    ];

    public function boot(): void
    {
        $this->commands($this->commands);
    }

    public function register(): void
    {
        $this->app->singleton('MigrateFresh', fn() => new MigrateFreshConsole());
        $this->app->singleton('MigrateRefresh', fn() => new MigrateRefreshConsole());
        $this->app->singleton('MigrateReset', fn(Application $app) => new MigrateResetConsole($app['migrator']));
        $this->app->singleton('MigrateRollback', fn(Application $app) => new MigrateRollbackConsole($app['migrator']));
        $this->app->singleton('MigrateStatus', fn(Application $app) => new MigrateStatusConsole($app['migrator']));
        $this->app->singleton('Migrate', fn(Application $app) => new MigrateConsole($app['migrator'], $app['events']));

        $this->app->singleton('DbSeed', fn(Application $app) => new DatabaseSeedConsole($app['db']));
        $this->app->singleton('DbWipe', fn(Application $app) => new DatabaseWipeConsole($app['db']));
    }
}
