<?php

namespace Pdffiller\LaravelInfluxProvider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Log\Writer;
use InfluxDB\Client as InfluxClient;
use InfluxDB\Client\Exception as ClientException;
use Monolog\Logger;
use Log;

class InfluxDBServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            $this->configPath() => config_path('influxdb.php')
        ]);

        $this->mergeConfigFrom($this->configPath(), 'influxdb');

        if (config('influxdb.use_monolog_handler') === 'true') {
            $handler = new InfluxDBMonologHandler($this->getLoggingLevel());
            $handler->setFormatter(new InfluxDBFormatter());

            $monolog = Log::getMonolog();
            $monolog->pushHandler($handler);

            $new_log = new Writer($monolog, Log::getEventDispatcher());
            Log::swap($new_log);
        }
    }

    public function register()
    {
        $this->app->singleton('InfluxDB\Client', function($app) {

            $protocol = 'influxdb';
            if (in_array(config('influxdb.protocol'), ['https', 'udp'])) {
                $protocol = config('influxdb.protocol') . '+' . $protocol;
            }
            try {
                return InfluxClient::fromDSN(
                    sprintf('%s://%s:%s@%s:%s/%s',
                        $protocol,
                        config('influxdb.user'),
                        config('influxdb.password'),
                        config('influxdb.host'),
                        config('influxdb.port'),
                        '' //config('influxdb.database')
                    ),
                    config('influxdb.timeout'),
                    (config('influxdb.verify_ssl') === 'true'),
                    config('influxdb.connect_timeout')
                );
            } catch (ClientException $e) {
                // die silently
                return null;
            }
        });
    }

    private function getLoggingLevel()
    {
        return in_array(config('influxdb.logging_level'), [
            'DEBUG',
            'INFO',
            'NOTICE',
            'WARNING',
            'ERROR',
            'CRITICAL',
            'ALERT',
            'EMERGENCY'
        ]) ? config('influxdb.logging_level') : Logger::NOTICE;
    }

    protected function configPath()
    {
        return __DIR__ . '/config/InfluxDB.php';
    }
}
