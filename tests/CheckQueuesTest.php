<?php

namespace Biigle\QueueAlert\Tests;

use Biigle\QueueAlert\CheckQueues;
use Biigle\QueueAlert\Notifications\Alert;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

class CheckQueuesTest extends TestCase
{
    public function testSendNoAlert()
    {
        Notification::fake();
        $check = app()->make(CheckQueues::class);
        $check();
        Notification::assertNothingSent();
    }

    public function testSendAlertDefault()
    {
        config(['queue-alert.email' => 'joe@example.com']);
        $notificationFake = Notification::fake();
        $fake = Queue::fake();
        for ($i=0; $i < 1001; $i++) {
            $fake->pushOn('default', new FakeJob);
        }
        $check = new CheckQueues($fake, app()->make(CacheFactory::class));
        $check();
        Notification::assertSentTo(new AnonymousNotifiable, Alert::class, function ($n) {
            $this->assertEquals('sync', $n->connection);
            $this->assertEquals('default', $n->queue);
            $this->assertEquals(1001, $n->size);

            return true;
        });
    }

    public function testSendAlertMaxJobsHigh()
    {
        config(['queue-alert.watch.0.max_jobs' => 2000]);
        Notification::fake();
        $fake = Queue::fake();
        for ($i=0; $i < 1001; $i++) {
            $fake->pushOn('default', new FakeJob);
        }
        $check = new CheckQueues($fake, app()->make(CacheFactory::class));
        $check();
        Notification::assertNothingSent();
    }

    public function testSendAlertMaxJobsLow()
    {
        config(['queue-alert.watch.0.max_jobs' => 1]);
        Notification::fake();
        $fake = Queue::fake();
        for ($i=0; $i < 2; $i++) {
            $fake->pushOn('default', new FakeJob);
        }
        $check = new CheckQueues($fake, app()->make(CacheFactory::class));
        $check();
        Notification::assertTimesSent(1, Alert::class);
    }

    public function testSendAlertConnection()
    {
        config(['queue-alert.watch.0.max_jobs' => 1]);
        config(['queue-alert.watch.0.connection' => 'sync']);
        Notification::fake();
        $fake = Queue::fake();
        for ($i=0; $i < 2; $i++) {
            $fake->pushOn('default', new FakeJob);
        }
        $check = new CheckQueues($fake, app()->make(CacheFactory::class));
        $check();
        Notification::assertTimesSent(1, Alert::class);
    }

    public function testSendAlertQueueOk()
    {
        config(['queue-alert.watch.0.max_jobs' => 1]);
        config(['queue-alert.watch.0.queue' => 'low']);
        Notification::fake();
        $fake = Queue::fake();
        for ($i=0; $i < 2; $i++) {
            $fake->pushOn('default', new FakeJob);
        }
        $check = new CheckQueues($fake, app()->make(CacheFactory::class));
        $check();
        Notification::assertNothingSent();
    }

    public function testSendAlertQueueAlert()
    {
        config(['queue-alert.watch.0.max_jobs' => 1]);
        config(['queue-alert.watch.0.queue' => 'low']);
        Notification::fake();
        $fake = Queue::fake();
        for ($i=0; $i < 2; $i++) {
            $fake->pushOn('low', new FakeJob);
        }
        $check = new CheckQueues($fake, app()->make(CacheFactory::class));
        $check();
        Notification::assertTimesSent(1, Alert::class);
    }

    public function testSendAlertEveryMinutes()
    {
        config(['queue-alert.watch.0.max_jobs' => 1]);
        config(['queue-alert.watch.0.report_every_minutes' => 60]);
        $config = config('queue-alert.watch.0');

        Notification::fake();
        $fake = Queue::fake();
        for ($i=0; $i < 2; $i++) {
            $fake->pushOn('default', new FakeJob);
        }
        $check = new CheckQueues($fake, app()->make(CacheFactory::class));
        $check();
        $check();
        Notification::assertTimesSent(1, Alert::class);
        Cache::put(CheckQueues::getLastReportCacheKey($config), Carbon::now()->subMinutes(61));
        $check();
        Notification::assertTimesSent(2, Alert::class);
    }

    public function testSendAlertEveryMinutesDisabled()
    {
        config(['queue-alert.watch.0.max_jobs' => 1]);
        config(['queue-alert.watch.0.report_every_minutes' => 1]);

        Notification::fake();
        $fake = Queue::fake();
        for ($i=0; $i < 2; $i++) {
            $fake->pushOn('default', new FakeJob);
        }
        $check = new CheckQueues($fake, app()->make(CacheFactory::class));
        $check();
        $check();
        Notification::assertTimesSent(2, Alert::class);
    }

    public function testSendAlertWaitMinutes()
    {
        $this->markTestIncomplete();
    }
}

class FakeJob {};
