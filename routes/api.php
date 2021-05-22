<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', 'App\Presenter\WelcomePresenter');

$router->post('/v1/routes', [
    'middleware' => 'auth:signature',
    'uses'       => Zeno\Management\Command\Synchronize::class,
]);
