<?php

declare(strict_types=1);

namespace Zeno\Http\Provider;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Zeno\Gateway\Protocol\Driver\Http\HttpClient;
use Zeno\Gateway\Protocol\Driver\Http\HttpRequestFactory;
use Zeno\Http\Client\Driver\GuzzleHttp;
use Zeno\Http\Client\GuzzleHttpRequestFactory;
use Zeno\Http\Presenter\Format\JsonFormatter;
use Zeno\Http\Presenter\Presenter;
use Zeno\Http\Service\Cors;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class HttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HttpClient::class, function (Application $app) {
            return new GuzzleHttp();
        });

        $this->app->singleton(HttpRequestFactory::class, function (Application $app) {
            return new GuzzleHttpRequestFactory($app);
        });

        $this->registerFormatters();

        $this->app->singleton(Presenter::class, function (Application $app) {
            return new Presenter(
                $app->tagged('zeno_presenter_formatters'),
                $app->get(JsonFormatter::class),
                $app->get(Cors::class)
            );
        });
    }

    private function registerFormatters(): void
    {
        $this->app->singleton(JsonFormatter::class);

        $this->app->tag([JsonFormatter::class], 'zeno_presenter_formatters');
    }
}
