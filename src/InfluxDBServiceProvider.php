<?php

namespace Pdffiller\LaravelInfluxProvider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Log\Writer;
use Monolog\Logger;
use Log;

class InfluxDBServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/InfluxDB.php' => config_path('influxdb.php')
        ]);

        if (config('influxdb.use_monolog_handler') === 'true') {
            $handler = new InfluxDBMonologHandler(Logger::NOTICE);
            $handler->setFormatter(new InfluxDBFormatter());

            $monolog = Log::getMonolog();
            $monolog->pushHandler($handler);

            $new_log = new Writer($monolog, Log::getEventDispatcher());
            Log::swap($new_log);
        }
    }

    public function register()
    {
        $this->app->singleton('InfluxDB\Database', function($app) {
            $protocol = 'influxdb';
            if (in_array(config('influxdb.protocol'), ['https', 'udp'])) {
                $protocol = config('influxdb.protocol') . '+' . $protocol;
            }
            try {
                $database = \InfluxDB\Client::fromDSN(
                    sprintf('%s://%s:%s@%s:%s/%s',
                        $protocol,
                        config('influxdb.user'),
                        config('influxdb.password'),
                        config('influxdb.host'),
                        config('influxdb.port'),
                        config('influxdb.database')
                    )
                );
            } catch (\Exception $e) {
                return null;
            }

            return $database;
        });
    }
}
