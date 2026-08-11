# Webware Log — Copilot Agent Instructions

## PHPUnit Mock vs Stub Rules

PHPUnit 13 enforces a strict separation between mocks and stubs. Violating these rules produces `PHPUnit Notices` that cause test suite failures under `failOnNotice="true"` (configured in `phpunit.xml.dist`).

### Rules

- **Use `createStub()`** when the test double only needs to return values (`method()->willReturn()`, `method()->willReturnCallback()`). No expectations are configured.
- **Use `createMock()`** only when the test verifies behavior with `expects()` (e.g. `expects($this->once())`, `expects($this->never())`).
- **Never** call `createMock()` without also calling `expects()` on at least one method — PHPUnit 13 will issue a notice.
- **Remove** `/** @var ClassName&MockObject */` annotations and `MockObject` intersection types on variables created with `createStub()`.
- **Remove** the `use PHPUnit\Framework\MockObject\MockObject;` import from any file where no `createMock()` + `expects()` usage remains.

### Examples

```php
// CORRECT — stub returns value, no expectations
$container = $this->createStub(ContainerInterface::class);
$container->method('get')->willReturnCallback(static fn(string $id): mixed => ...);

// CORRECT — mock verifies behavior with expects()
$handler = $this->createMock(ErrorHandler::class);
$handler->expects($this->once())->method('attachListener');

// WRONG — createMock() with no expects() triggers PHPUnit notice
$logger = $this->createMock(Logger::class);
$logger->method('withName')->willReturn($logger); // should be createStub()
```

## PHPUnit Coverage Metadata Rules

`phpunit.xml.dist` has `requireCoverageMetadata="true"`. Every test class **must** have:

1. `#[CoversClass(ClassName::class)]` — one per source class under test.
2. `#[CoversMethod(ClassName::class, 'methodName')]` — one per public/protected method exercised.
3. `use PHPUnit\Framework\Attributes\CoversClass;` and `use PHPUnit\Framework\Attributes\CoversMethod;` imports.

Tests without coverage metadata produce PHPUnit notices that fail CI.

Source files in `src/Handler/LaminasDbHandler.php` and `src/Handler/LaminasDbHandlerFactory.php` are **excluded** from coverage (`phpunit.xml.dist` `<exclude>` block) because `laminas/laminas-db` conflicts with `php-db/phpdb-mysql` and cannot be installed simultaneously.

## laminas/laminas-db Conflict

`laminas/laminas-db` and `php-db/phpdb-mysql` register the same PSR-11 container entry and **cannot coexist**. Do not:

- Add `laminas/laminas-db` to `composer.json`.
- Write tests that reference `Laminas\Db\Adapter\AdapterInterface`.
- Attempt to test `LaminasDbHandler` or `LaminasDbHandlerFactory`.

## Integration Test Database

Integration tests connect to MySQL via env vars (`TESTS_ADAPTER_MYSQL_*`). Both the fixture loader (`MysqlFixtureLoader`) and the test class (`PhpDbHandlerTest`) use these same env var names. The Docker Compose MySQL service uses a fixed root password (`MYSQL_ROOT_PASSWORD=password`). `phpunit.xml.dist` sets matching credentials.

## .laminas-ci.json

**Never edit `.laminas-ci.json`.** This file is managed by the project owner and controls the laminas CI pipeline configuration.
