<?php

namespace Pdffiller\LaravelInfluxProvider;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class InfluxDBMonologHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        $point = [
            new \InfluxDB\Point(
                isset($record['name']) ? $record['name'] : 'name',
                isset($record['value']) ? $record['value'] : 0,
                isset($record['tags']) && is_array($record['tags']) ? $record['tags'] : [],
                isset($record['fields']) && is_array($record['fields']) ? $record['fields'] : [],
                isset($record['timestamp']) ? $record['timestamp'] : exec('date +%s%N')  // timestamp in nanoseconds on Linux ONLY
            )
        ];
        try {
            \Influx::writePoints($point);
        } catch (\InfluxDB\Exception $e) {
            \Log::notice('Influx write Exception', [
                'event' => $record,
                'message' => $e->getMessage()
            ]);
        }
    }
}