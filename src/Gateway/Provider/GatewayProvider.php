<?php

declare(strict_types=1);

namespace Zeno\Gateway\Provider;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Zeno\Gateway\Action\ActionManager;
use Zeno\Gateway\Action\Handler\AggregateActionHandler;
use Zeno\Gateway\Action\Handler\SingleActionHandler;
use Zeno\Gateway\Protocol\Driver\Http\HttpProtocol;
use Zeno\Gateway\Protocol\ProtocolManager;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class GatewayProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->singleton(ActionManager::class, function (Application $app) {
            return new ActionManager($app->tagged('zeno_action_handlers'));
        });

        $this->app->singleton(ProtocolManager::class, function (Application $app) {
            return new ProtocolManager($app->tagged('zeno_protocols'));
        });
    }

    public function register(): void
    {
        $this->registerProtocols();
        $this->registerActionHandlers();
    }

    private function registerProtocols(): void
    {
        $this->app->singleton(HttpProtocol::class);

        $this->app->tag([HttpProtocol::class], 'zeno_protocols');
    }

    private function registerActionHandlers(): void
    {
        $this->app->singleton(SingleActionHandler::class);
        $this->app->singleton(AggregateActionHandler::class);

        $this->app->tag(
            [
                SingleActionHandler::class,
                AggregateActionHandler::class,
            ],
            'zeno_action_handlers'
        );
    }
}
