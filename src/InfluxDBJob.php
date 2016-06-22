<?php

namespace Pdffiller\LaravelInfluxProvider;

use Log;
use Exception;
use Influx; // Should be declared Facade from InfluxDBFacade
use InfluxDB\Point;
use Resque_Exception;

class InfluxDBJob
{
    public $args;

    public function __construct(array $args = [])
    {
        if (count($args)) {
            $this->args = $args;
        }
    }

    public function perform()
    {
        $event = $this->args;
        $point = [
            new Point(
                isset($event['name']) ? $event['name'] : 'name',
                isset($event['value']) ? $event['value'] : 1,
                isset($event['tags']) && is_array($event['tags']) ? $event['tags'] : [],
                isset($event['fields']) && is_array($event['fields']) ? $event['fields'] : [],
                isset($event['timestamp']) ? (int)$event['timestamp'] : (int)exec('date +%s%N')  // timestamp in nanoseconds on Linux ONLY
            )
        ];

        $result = false;

        try {
            $result = Influx::writePoints($point);
        } catch (Exception $e) {
            $this->job->fail(new Resque_Exception());
        }

        return $result;
    }
}
