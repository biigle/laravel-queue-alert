<?php

namespace Biigle\QueueAlert\Tests;

use Biigle\QueueAlert\QueueAlertServiceProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
      * Boots the application.
      *
      * @return \Illuminate\Foundation\Application
      */
    public function createApplication()
    {
        // We create a full Laravel app here for testing purposes. The tests
        // need access to the application config and the cache.
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $app->register(QueueAlertServiceProvider::class);

        return $app;
    }
}
