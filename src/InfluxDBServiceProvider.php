<?php

namespace Pdffiller\LaravelInfluxProvider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Log\Writer;
use Log;

class InfluxDBServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/InfluxDB.php' => config_path('influxdb.php')
        ]);

        $handler = new InfluxDBMonologHandler();

        $monolog = Log::getMonolog();
        $monolog->pushHandler($handler);

        $new_log = new Writer($monolog, Log::getEventDispatcher());
        Log::swap($new_log);
    }

    public function register()
    {
        $this->app->singleton('InfluxDB\Database', function($app) {
            $protocol = config('influxdb.protocol') === 'http' ? '' : config('influxdb.protocol').'+';
            $database = \InfluxDB\Client::fromDSN(
                sprintf('%sinfluxdb://%s:%s@%s:%s/%s',
                    $protocol,
                    config('influxdb.user'),
                    config('influxdb.password'),
                    config('influxdb.host'),
                    config('influxdb.port'),
                    config('influxdb.database')
                )
            );

            return $database;
        });
    }
}
