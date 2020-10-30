<?php

return [
    'debug'                  => env('APP_DEBUG', false),
    'caller'                 => [
        'handler' => \Zeno\Router\Handler\RouteHandler::class,
    ],
    'version'                => '1.0.0',
    'response_header_prefix' => 'Zeno-Gateway-',
    'response_header_via'    => 'Zeno',
];
