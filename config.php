<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('beebmx/kirby-db', [
    'options' => [
        'default' => 'sqlite',
        'drivers' => [],
        'eloquent' => false
    ],
]);
