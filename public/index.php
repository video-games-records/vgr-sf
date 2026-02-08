<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    if (in_array($context['APP_ENV'], ['prod', 'staging'], true)) {
        $kernel = new HttpCache($kernel);
    }

    return $kernel;
};
