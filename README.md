# Laravel InfluxDB provider
A minimal service provider to set up and use InfluxDB SDK in Laravel 5

### Installation
- Add a line to the *require* section of `composer.json` and execute `$ composer install`
```js
"require": {
//  ...
    "pdffiller/laravel-influx-provider": "^1.6"
}
```
- Add these lines to `config/app.php`
```php
'providers' => [
//  ...
    Pdffiller\LaravelInfluxProvider\InfluxDBServiceProvider::class,
]


'aliases' => [
// ...
    'Influx' => Pdffiller\LaravelInfluxProvider\InfluxDBFacade::class,
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
LARAVEL_INFLUX_PROVIDER_VERIFY_SSL=false
LARAVEL_INFLUX_PROVIDER_TIMEOUT=0
LARAVEL_INFLUX_PROVIDER_CONNECT_TIMEOUT=0
```

### How to use
```php
$client = new \Influx;
$data   = $client::query('SELECT * from "data" ORDER BY time DESC LIMIT 1');
```

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

Also you can send data to another database like this:
```php
Influx::selectDB($dbName)->writePoints($point);
```

## License

[airSlate](https://airslate.com/) and any contributors to this project each grants you a license, under its respective
copyrights, to the Laravel InfluxDB provider and other content in this repository under the
MIT License, see the [LICENSE](LICENSE) file for more information. <br>
