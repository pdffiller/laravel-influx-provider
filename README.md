# Laravel InfluxDB provider
A minimal service provider to set up and use InfluxDB SDK in Laravel 5

### Installation
- Add a line to *require* section of `composer.json` and execute `$ composer install`
```js
"require": {
//  ...
    "pdffiller/laravel-influx-provider": "^1.1"
}
```
- Add a line to `config/app.php`
```php
'providers' => [
//  ...
    Pdffiller\LaravelInfluxProvider\InfluxDBServiceProvider::class,
]
```
- Define env variables to connect to InfluxDB
```
LARAVEL_INFLUX_PROVIDER_PROTOCOL=http
LARAVEL_INFLUX_PROVIDER_USER=some_user
LARAVEL_INFLUX_PROVIDER_PASSWORD=some_password
LARAVEL_INFLUX_PROVIDER_HOST=host
LARAVEL_INFLUX_PROVIDER_PORT=8086
LARAVEL_INFLUX_PROVIDER_DATABASE=database_name
```

### How to use
```php
$point = [
    new \InfluxDB\Point(
        'name' => 'some_name',
        'value' => 1, // some value for some_name
        'tags' => [
            // array of string values
        ],
        'fields' => [
            // array of numeric values
        ],
        'timestamp' => exec('date +%s%N')  // timestamp in nanoseconds on Linux ONLY
    )
];
try {
    Influx::writePoints($point);
} catch (\InfluxDB\Exception $e) {
    // something is wrong, track this
}
```
