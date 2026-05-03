# axleus/axleus-log

[![PHP Version](https://img.shields.io/packagist/php-v/axleus/axleus-log)](https://packagist.org/packages/axleus/axleus-log)
[![Latest Stable Version](https://img.shields.io/packagist/v/axleus/axleus-log)](https://packagist.org/packages/axleus/axleus-log)
[![License](https://img.shields.io/github/license/axleus/axleus-log)](LICENSE)
[![Continuous Integration](https://github.com/axleus/axleus-log/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/axleus/axleus-log/actions/workflows/continuous-integration.yml)
[![Coverage Status](https://coveralls.io/repos/github/axleus/axleus-log/badge.svg?branch=0.1.x)](https://coveralls.io/github/axleus/axleus-log?branch=0.1.x)

This package provides logging via Monolog for Mezzio (PSR-15) applications.
It provides log handlers backed by `laminas-db` or `php-db/phpdb` for writing logs to a database table.
It also provides a PSR-14 event listener and a Laminas EventManager bridge listener for error logging.

## Documentation

- [Installation](docs/installation.md)
- [Configuration Reference](docs/configuration.md)
- [Middleware](docs/middleware.md)
- [Handlers](docs/handlers.md)
- [Processors](docs/processors.md)
- [Event-Driven Logging (PSR-14)](docs/events.md)
- [Error Logging](docs/error-logging.md)

## Quick Start

Install the package and let `laminas-component-installer` inject the `ConfigProvider`:

```bash
composer require axleus/axleus-log
```

Import the database schema and add the package's `ConfigProvider` to your config aggregator if not done automatically. Then pipe the middleware into your application pipeline:

```php
// config/pipeline.php
$app->pipe(\Axleus\Log\Middleware\MonologMiddleware::class);
```

Enable optional features via config:

```php
// config/autoload/log.local.php
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => [
        'log_errors'     => true,  // auto-log uncaught exceptions
        'process_uuid'   => true,  // add UUID v7 to every record
        'channel'        => 'app', // LogChannel enum value
    ],
];
```

See the [full documentation](docs/) for details on all options, handlers, processors, and event-driven logging.
