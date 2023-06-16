<?php

namespace Biigle\QueueAlert;

use Biigle\QueueAlert\Notifications\Alert;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Support\Facades\Notification;

class CheckQueues
{
   /**
    * The queue manager instance.
    *
    * @var QueueFactory
    */
   protected $queue;

   /**
    * The cache manager instance.
    *
    * @var CacheFactory
    */
   protected $cache;

   /**
    * Get the cache key for the stored time of the last notification for a queue and
    * connection.
    */
   public static function getLastReportCacheKey(array $config): string
   {
      return "queue-alert-last-report-{$config['connection']}-{$config['queue']}";
   }

   /**
    * Get the cache key for the stored time of the first alert registered for a queue and
    * connection.
    */
   public static function getFirstAlertCacheKey(array $config): string
   {
      return "queue-alert-first-alert-{$config['connection']}-{$config['queue']}";
   }

   /**
    * Create a new instance.
    */
   public function __construct(QueueFactory $queue, CacheFactory $cache)
   {
      $this->queue = $queue;
      $this->cache = $cache;
   }

   /**
    * Run the check.
    */
   public function __invoke()
   {
      $watch = config('queue-alert.watch');
      foreach ($watch as $item) {
         $this->checkQueue($item);
      }
   }

   /**
    * Check a single queue and send an alert if necessary.
    *
    * @param array $config
    */
   protected function checkQueue(array $config)
   {
      $connection = $config['connection'] ?: config('queue.default');
      $size = $this->queue->connection($connection)->size($config['queue']);
      if ($size > $config['max_jobs']) {
         $report = true;

         $everyMinutes = $config['report_every_minutes'];
         if ($everyMinutes > 0) {
            $lastReport = $this->cache->get(static::getLastReportCacheKey($config));

            if (!is_null($lastReport) && Carbon::now()->subMinutes($everyMinutes) < Carbon::parse($lastReport)) {
               $report = false;
            }
         }

         $waitMinutes = $config['report_wait_minutes'];

         if ($waitMinutes > 0) {
            $firstAlert = $this->cache->get(static::getFirstAlertCacheKey($config));
            if (is_null($firstAlert)) {
               $firstAlert = Carbon::now();
               $this->cache->put(static::getFirstAlertCacheKey($config), $firstAlert);
            } else {
               $firstAlert = Carbon::parse($firstAlert);
            }

            if (Carbon::now()->subMinutes($waitMinutes) < $firstAlert) {
               $report = false;
            }
         }

         if ($report) {
            $this->cache->put(static::getLastReportCacheKey($config), Carbon::now());
            Notification::route('mail', config('queue-alert.email'))
               ->notify(new Alert($connection, $config['queue'], $size));
         }
      } else {
         $this->cache->forget(static::getFirstAlertCacheKey($config));
      }
   }
}
