<?php

declare(strict_types=1);

namespace App\Provider;

use App\Guard\SignatureGuard;
use Illuminate\Support\ServiceProvider;
use Zeno\Http\Request\RequestStack;
use Zeno\Signature\Signer;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['auth']->extend('signature', function ($app) {
            return new SignatureGuard($app[RequestStack::class], new Signer());
        });
    }
}
