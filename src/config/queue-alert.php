<?php

return [

    /*
    | The email address to send alert emails to.
    */
    'email' => env('QUEUE_ALERT_EMAIL'),

    /*
    | Alert rules for every queue and connection to watch.
    */
    'watch' => [
        [
            // null is the default connection.
            'connection' => null,
            // Name of the queue to monitor.
            'queue' => 'default',
            // Send alert if the number of jobs exceeds this threshold.
            'max_jobs' => 1000,
            // Send an alert only every x minutes. This requires a persistent cache that is available for all scheduled command runners.
            'report_every_minutes' => 60,
            // The queue size must exceed the threshold for x minutes before an alert is
            // sent.  This requires a persistent cache that is available for all
            // scheduled command runners.
            'report_wait_minutes' => 0,
        ],
    ],
];
