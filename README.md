# Queue Alert 🚨

A package to send alert emails if a Laravel queue is very busy.

[![Tests](https://github.com/biigle/laravel-queue-alert/actions/workflows/tests.yml/badge.svg)](https://github.com/biigle/laravel-queue-alert/actions/workflows/tests.yml)

## Installation

```
composer require biigle/laravel-cached-openstack
```

## Usage

Set the `QUEUE_ALERT_EMAIL` variable in your `.env` file. By default, this package will send one alert email every hour when the `default` queue of the default connection exceeds 1000 jobs.

## Configuration

Run the following command to publish the configuration:

```
php artisan vendor:publish --tag=config --provider=\\Biigle\\QueueAlert\\QueueAlertServiceProvider
```

### Multiple queues and connections

Add more items to the `watch` array of the configuration. The default configuration already provides an example for an array antry. Alerts will be sent separately for each configured entry.

### Report interval

Update the `report_every_minutes` value for an entry in the `watch` array. An alert is only send once every x minutes based on the configures value.

### Report patience

Update the `report_wait_minutes` value for an entry in the `watch` array. If this is greater than 0, an alert will be sent only if the number of jobs exceeds the configured threshold for x minutes based on the configured value.
