<?php

namespace Pdffiller\LaravelInfluxProvider;

use Queue;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class InfluxDBMonologHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     * @return void
     */
    protected function write(array $record)
    {
        $event = $record;
        if (isset($record['formatted'])) {
            $event = $record['formatted'];
        }
        if (isset($event['timestamp'])) {
            $event['timestamp'] = (int) $event['timestamp']; // Monolog automatically cast it as String
        }
        if ( config('influxdb.use_queue') === 'true') {
            Queue::push(InfluxDBJob::class, $event, 'influx');
            return;
        }

        $influxWriter = new InfluxDBJob($event);
        $influxWriter->perform();
    }
}
