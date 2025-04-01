# axleus/axleus-log

This package provides logging via Monolog for all laminas-mvc and mezzio applications.
It provides a log handler backed by laminas-db for writing logs to a database table.
It also provides event listeners for error logging in both frameworks.

## Mezzio Integration

Just pipe the middleware into your application as early as required to provide logging for all future middleware/handlers. If you are using the default Mezzio pipeline, this should be done in the `config/pipeline.php` file. If you are delegating your pipeline to configuration you can
uncomment the following in the ConfigProvider class.

```php
'middleware_pipeline' => $this->getPipelineConfig(),
```

To enable error logging in Mezzio or MVC simply provide the following top level config key from any ConfigProvider.

```php
return [
    \Axleus\Log\ConfigProvider::class => [
        'log_errors' => true,
    ],
];
```

## Default Channels

By default, the following channels are provided for logging:

- app
- error
- user

To provide some safety around channels the module provides Axleus\Log\LogChannel which is a BackedEnum.

Finally, you can import the file ./test/integration/TestFixtures/mysql.sql into your database to create the required table for logging. If you are not using mysql, then you can use it as a guide to create the table in your database of choice as long as its supported by laminas-db.

Everything else _should_ just work out of the box.
