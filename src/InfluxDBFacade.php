<?php

namespace Pdffiller\LaravelInfluxProvider;

use Illuminate\Support\Facades\Facade;

class InfluxDBFacade extends Facade {
    protected static function getFacadeAccessor() {
        return 'InfluxDB\Database';
    }
}