<?php

use Kirby\Cms\App as Kirby;

@include_once __DIR__.'/vendor/autoload.php';

Kirby::plugin('beebmx/kirby-db', [
    'options' => require_once __DIR__.'/extensions/options.php',
]);
