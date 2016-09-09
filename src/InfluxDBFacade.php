<?php

namespace Pdffiller\LaravelInfluxProvider;

use Illuminate\Support\Facades\Facade;

class InfluxDBFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'InfluxDB\Client';
    }

    /**
     * @param array ...$params
     * @return bool
     */
    public static function writePoints(...$params)
    {
        $instance = static::getFacadeRoot();

        return $instance->selectDB(config('influxdb.database'))->writePoints(...$params);
    }
}