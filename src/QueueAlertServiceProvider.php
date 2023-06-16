<?php

namespace Biigle\QueueAlert;

use Biigle\QueueAlert\CheckQueues;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;


class QueueAlertServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/queue-alert.php' => base_path('config/queue-alert.php'),
        ], 'config');

        if (method_exists($this->app, 'booted')) {
            // Wait for Laravel to boot before adding the scheduled event.
            // See: https://stackoverflow.com/a/36630136/1796523
            $this->app->booted([$this, 'registerScheduledCommands']);
        } else {
            // Lumen has no 'booted' method but it works without, too, for some reason.
            $this->registerScheduledCommands($this->app);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/queue-alert.php', 'queue-alert');
    }

    /**
     * Register the scheduled command to prune the file cache.
     *
     * @param mixed $app Laravel or Lumen application instance.
     */
    public function registerScheduledCommands($app)
    {
        $app->make(Schedule::class)
            ->call($app->make(CheckQueues::class))
            ->everyMinute()
            ->name('check-queue-alert')
            ->onOneServer();
    }
}
