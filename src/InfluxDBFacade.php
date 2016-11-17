<?php

namespace Pdffiller\LaravelInfluxProvider;

use Illuminate\Support\Facades\Facade;

class InfluxDBFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'InfluxDB\Client';
    }

    public static function __callStatic($method, $arguments)
    {
        return static::getFacadeRoot()
            ->selectDB(config('influxdb.database'))
            ->$method(...$arguments)
        ;
    }
}
