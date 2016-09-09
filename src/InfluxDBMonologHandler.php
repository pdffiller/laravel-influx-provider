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
        if ( config('influxdb.use_queue') === 'true') {
            Queue::push(InfluxDBJob::class, $event, config('influxdb.queue_name'));
            return;
        }

        $influxWriter = new InfluxDBJob($event);
        $influxWriter->perform();
    }
}
