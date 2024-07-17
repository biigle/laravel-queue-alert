<?php

namespace Biigle\QueueAlert\Tests;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;

class QueueAlertServiceProviderTest extends TestCase
{
    public function testScheduledCommand()
    {
        $schedule = $this->app[Schedule::class];
        $events = $schedule->events();
        $event = last($events);
        $this->assertEquals('check-queue-alert', $event->description);
    }
}
