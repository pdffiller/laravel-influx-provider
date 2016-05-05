<?php

namespace Pdffiller\LaravelInfluxProvider;

use Influx; // Should be declared Facade from InfluxDBFacade

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
            new \InfluxDB\Point(
                isset($event['name']) ? $event['name'] : 'name',
                isset($event['value']) ? $event['value'] : 0,
                isset($event['tags']) && is_array($event['tags']) ? $event['tags'] : [],
                isset($event['fields']) && is_array($event['fields']) ? $event['fields'] : [],
                isset($event['timestamp']) ? $event['timestamp'] : exec('date +%s%N')  // timestamp in nanoseconds on Linux ONLY
            )
        ];
        try {
            Influx::writePoints($point);
        } catch (\InfluxDB\Exception $e) {
            \Log::notice('Influx write Exception', [
                'event' => $event,
                'message' => $e->getMessage()
            ]);
        }
    }
}
