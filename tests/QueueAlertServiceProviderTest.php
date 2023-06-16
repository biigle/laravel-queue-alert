<?php

namespace Biigle\QueueAlert\Tests;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;

class QueueAlertServiceProviderTest extends TestCase
{
    public function testScheduledCommand()
    {
        $schedule = $this->app[Schedule::class];
        $event = $schedule->events()[0];
        $this->assertEquals('check-queue-alert', $event->description);
    }
}
