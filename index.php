<?php

use Kirby\Cms\App as Kirby;

@include_once __DIR__.'/vendor/autoload.php';

Kirby::plugin('beebmx/kirby-db', [
    'options' => [
        'default' => 'sqlite',
        'drivers' => [

        ],
        'eloquent' => false,
    ],
]);
