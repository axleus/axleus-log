# Installation

## Requirements

- PHP 8.4.1 or later
- A Mezzio application (PSR-15 / PSR-11 container)

## Composer

```bash
composer require webware/webware-log
```

`laminas/laminas-component-installer` will prompt you to inject `Webware\Log\ConfigProvider` automatically into your application's config aggregator. Accept the prompt or add it manually:

```php
// config/config.php
new Webware\Log\ConfigProvider(),
```

## Database Table

One SQL fixture is provided for MySQL. For any other database supported by `laminas-db` use the fixture as a template.

```bash
# import from the project root
mysql -u root -p my_database < vendor/webware/webware-log/test/integration/TestFixtures/mysql.sql
```

The fixture creates a `log` table with the following columns:

| Column | Type | Notes |
|---|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT` | Primary key |
| `channel` | `VARCHAR(255)` | Monolog channel name |
| `level` | `VARCHAR(50)` | Log level name (e.g. `ERROR`) |
| `uuid` | `VARCHAR(36)` | UUID v7 (populated when `process_uuid: true`) |
| `message` | `TEXT` | Formatted log message |
| `time` | `INT UNSIGNED` | Unix timestamp |
| `user_identifier` | `VARCHAR(255)` | Authenticated user identity (nullable) |
