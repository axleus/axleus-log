# Project Architecture Blueprint — `axleus/axleus-log`

> **Version:** 0.0.x (branch: `add-phpdb-handler`)  
> **License:** BSD-3-Clause  
> **PHP requirement:** ~8.4.0 || ~8.5.0 || ~8.6.0

---

## Table of Contents

1. [Architectural Overview](#1-architectural-overview)
2. [Architecture Visualization](#2-architecture-visualization)
3. [Core Architectural Components](#3-core-architectural-components)
4. [Architectural Layers and Dependencies](#4-architectural-layers-and-dependencies)
5. [Data Architecture](#5-data-architecture)
6. [Cross-Cutting Concerns](#6-cross-cutting-concerns)
7. [Technology-Specific Patterns](#7-technology-specific-patterns)
8. [Implementation Patterns](#8-implementation-patterns)
9. [Testing Architecture](#9-testing-architecture)
10. [Deployment Architecture](#10-deployment-architecture)
11. [Extension and Evolution Patterns](#11-extension-and-evolution-patterns)
12. [Architectural Decision Records](#12-architectural-decision-records)
13. [Architecture Governance](#13-architecture-governance)

---

## 1. Architectural Overview

`axleus-log` is a **PHP logging component** designed to integrate [Monolog v3](https://seldaek.github.io/monolog/) into [Mezzio](https://docs.mezzio.dev/) and [Laminas MVC](https://docs.laminas.dev/) applications. It acts as a thin, opinionated adapter layer that:

- Exposes PSR-3 (`LoggerInterface`) through the PSR-11 dependency injection container.
- Bridges Laminas's `EventManager` event bus to the PSR-3 logger via a listener.
- Integrates with the Mezzio PSR-15 middleware pipeline to enrich log records with authenticated-user identity.
- Provides Monolog handlers that persist log records to a relational database using either `laminas-db` or `webware/phpdb`.
- Decorates Mezzio's built-in `ErrorHandler` so that uncaught exceptions are automatically logged.

### Guiding Principles

| Principle | Implementation |
|---|---|
| PSR compliance | PSR-3 logger, PSR-11 container, PSR-15 middleware |
| Laminas component model | `ConfigProvider` + `laminas-component-installer` |
| Loose coupling | All classes wired via factories; no service-locator anti-pattern inside domain classes |
| Dual runtime support | `Runtime` enum distinguishes Mezzio vs. Laminas MVC wiring |
| Optional features | i18n translation and UUID enrichment are guarded by container availability checks |
| Non-invasive integration | `MezzioErrorHandlerDelegator` adds logging without replacing the framework's error handler |

### Architectural Boundaries

```
[PSR-15 Middleware Pipeline]
        │
   MonologMiddleware  ──► enriches records with user identity; attaches logger to request
        │
[Monolog\Logger] ──► pushHandler(LaminasDbHandler | PhpDbHandler)
                 └──► pushProcessor(RamseyUuidProcessor, PsrLogMessageProcessor, [LaminasI18nProcessor])
        │
[Database Handler] ──► laminas-db Sql | webware/phpdb Sql
        │
[MySQL Table: log]
```

---

## 2. Architecture Visualization

### C4 — Context Diagram

```
┌──────────────────────────────────────────────────────────────────────┐
│  Mezzio / Laminas Application                                        │
│                                                                      │
│  ┌─────────────────┐   uses   ┌───────────────────────────────────┐ │
│  │  Application     │ ───────► │  axleus/axleus-log                │ │
│  │  Code (handlers, │          │  (PSR-3 logging component)        │ │
│  │  controllers)    │          └───────────────────────────────────┘ │
│  └─────────────────┘                         │                       │
│                                              │ writes                │
│                                   ┌──────────▼──────────┐           │
│                                   │  MySQL Database      │           │
│                                   │  (log table)         │           │
│                                   └─────────────────────┘           │
└──────────────────────────────────────────────────────────────────────┘
```

### C4 — Component Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  axleus/axleus-log                                                           │
│                                                                              │
│  ┌──────────────┐   builds   ┌─────────────────────────────────────────┐   │
│  │  LogFactory  │ ──────────► │  Monolog\Logger (PSR-3 LoggerInterface) │   │
│  └──────────────┘             │    • RamseyUuidProcessor                │   │
│                               │    • PsrLogMessageProcessor             │   │
│                               │    • [LaminasI18nProcessor]             │   │
│                               │    • LaminasDbHandler | PhpDbHandler    │   │
│                               └─────────────────────────────────────────┘   │
│                                                                              │
│  ┌────────────────────────┐  decorates  ┌──────────────────────────────┐   │
│  │ MezzioErrorHandler     │ ──────────── │  Laminas Stratigility        │   │
│  │ Delegator              │             │  ErrorHandler                 │   │
│  └────────────────────────┘             └──────────────────────────────┘   │
│          │ attaches                                                          │
│  ┌───────▼────────────┐                                                     │
│  │ MezzioErrorListener│  (logs Throwable + request + response)              │
│  └────────────────────┘                                                     │
│                                                                              │
│  ┌──────────────────────┐  implements PSR-15  ┌─────────────────────────┐  │
│  │  MonologMiddleware   │ ──────────────────── │  Mezzio Pipeline        │  │
│  └──────────────────────┘                     └─────────────────────────┘  │
│          │ injects user identity                                             │
│          │ attaches logger to request attribute                              │
│                                                                              │
│  ┌──────────────────────────┐  listens to  ┌──────────────────────────┐    │
│  │  Psr3LogLaminasListener  │ ─────────────│ Laminas SharedEventManager│    │
│  └──────────────────────────┘              └──────────────────────────┘    │
│          │ translates LogEvent → PSR-3 log call                             │
│                                                                              │
│  ┌─────────────────────┐   ┌───────────────────────────────┐               │
│  │  LaminasDbHandler   │   │  PhpDbHandler                 │               │
│  │  (laminas-db Sql)   │   │  (webware/phpdb Sql)          │               │
│  └─────────────────────┘   └───────────────────────────────┘               │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Data Flow — Request Log Entry

```
HTTP Request
    │
    ▼
MonologMiddleware.process()
    │  pushProcessor(fn: extract UserInterface identity → extra['email'])
    │  request->withAttribute(LoggerInterface, $logger)
    ▼
Handler / Controller
    │  $logger->info('Something happened', $context)
    ▼
Monolog\Logger.log()
    │  → RamseyUuidProcessor    → adds extra['uuid'] (UUID v7)
    │  → PsrLogMessageProcessor → interpolates {placeholders}
    │  → LaminasI18nProcessor   → translates message (optional)
    ▼
LaminasDbHandler | PhpDbHandler .write(LogRecord)
    │  builds INSERT: channel, level, uuid, message, time, user_identifier, [context JSON]
    ▼
MySQL log table
```

### Data Flow — Laminas EventManager Event

```
Controller / Service
    │  $events->trigger(LogEvent::EVENT_LOG_ERROR, $this, $params)
    ▼
Laminas SharedEventManager
    ▼
Psr3LogLaminasListener.onLog(EventInterface)
    │  extracts: level, message, context, channel
    │  optionally switches logger channel via withName()
    ▼
Monolog\Logger.log()  →  handler chain  →  DB
```

### Data Flow — Uncaught Exception

```
Mezzio ErrorHandler.process()
    │  catches Throwable
    ▼
MezzioErrorListener.__invoke(Throwable, Request, Response)
    │  $logger->error($e->getMessage(), [exception, request, response])
    ▼
Monolog\Logger  →  handler chain  →  DB  (channel: 'error')
```

---

## 3. Core Architectural Components

### 3.1 Enums (`src/LogChannel.php`, `src/Runtime.php`)

| Enum | Cases | Purpose |
|---|---|---|
| `LogChannel` | `Audit`, `Analytics`, `App`, `Debug`, `Error`, `System`, `User`, `Security` | Type-safe log channel names used throughout the component |
| `Runtime` | `Mezzio`, `Mvc` | Identifies the host framework to allow conditional wiring |

**Design decision:** Using backed string enums makes configuration values refactorable at the call-site without stringly-typed magic strings scattered across consumers.

---

### 3.2 `ConfigProvider` (`src/ConfigProvider.php`)

The root wiring class for the Laminas component installer. When invoked, it returns:

```php
[
    'dependencies'  => [...],   // DI factories, delegators, invokables
    'listeners'     => [...],   // event listener classes to attach
    'log_runtime'   => 'mezzio',
    'templates'     => [...],   // Laminas\View template path
    ConfigProvider::class => [  // component-scoped config key
        'channel'             => 'app',
        'log_errors'          => false,
        'process_uuid'        => false,
        'process_translation' => false,
        'table'               => 'log',
    ],
]
```

**Key pattern:** component config is namespaced under `ConfigProvider::class` (the FQCN string) to prevent collisions with other packages.

---

### 3.3 Container (Factories & Delegators) (`src/Container/`)

| Class | Type | Builds | Notes |
|---|---|---|---|
| `LogFactory` | Factory | `Monolog\Logger` as `LoggerInterface` | Pushes handlers and processors in order |
| `MezzioErrorHandlerDelegator` | Delegator | `Laminas\Stratigility\Middleware\ErrorHandler` | Conditionally attaches `MezzioErrorListener` when `log_errors = true` |

The delegator pattern means the error handler is extended non-destructively — if `log_errors` is `false`, the original handler is returned unmodified.

---

### 3.4 Handlers (`src/Handler/`)

Both handlers extend `Monolog\Handler\AbstractProcessingHandler` and write one row per log record to a relational database table.

| Class | DB Abstraction | Notes |
|---|---|---|
| `LaminasDbHandler` | `laminas/laminas-db` `Sql` | Original implementation; uses `AdapterInterface` |
| `PhpDbHandler` | `webware/phpdb` `Sql` | Newer handler (current branch); stores `context`+`extra` as JSON; column name is `user_identifier` (vs. `userIdentifier` in Laminas variant) |

**Shared write columns:** `channel`, `level`, `uuid`, `message`, `time`, `user_identifier`  
**PhpDbHandler-only column:** `context` (JSON-encoded merged context + filtered extra)

**Factory pattern:** Each handler has a corresponding `*Factory` class that reads config, resolves the DB adapter from the container, and injects authentication config (`authentication.username`) to know which `extra` key carries the user identity.

---

### 3.5 Event (`src/Event/LogEvent.php`)

`LogEvent` extends `Laminas\EventManager\Event` and acts as the bridge between Laminas's event system and Monolog's level/channel model.

- Event name is set to the PSR-3 log level string (e.g., `"error"`) via the `Level::toPsrLogLevel()` mapping.
- Typed accessor methods (`getLevel()`, `getMessage()`, `getChannel()`, `getExtra()`, `getContext()`, `getUuid()`) wrap the underlying `$params` array.
- Callers create a `LogEvent` and trigger it on a Laminas `EventManager`; the `Psr3LogLaminasListener` handles it.

---

### 3.6 Listeners (`src/Listener/`)

| Class | Trigger mechanism | Responsibility |
|---|---|---|
| `MezzioErrorListener` | Attached to Mezzio `ErrorHandler` via delegator | Logs uncaught `Throwable` with request/response context |
| `Psr3LogLaminasListener` | `Laminas\EventManager\AbstractListenerAggregate` via `SharedEventManager` | Bridges `LogEvent` to PSR-3 logger |

`Psr3LogLaminasListener` attaches to all events on three shared identifiers: `AbstractController`, `MiddlewareInterface`, and `RequestHandlerInterface`. It also attaches once per Monolog `Level` case (7 levels × 3 identifiers).

---

### 3.7 Middleware (`src/Middleware/MonologMiddleware.php`)

A PSR-15 `MiddlewareInterface` that runs early in the Mezzio pipeline to:

1. Extract the authenticated `UserInterface` from the request attributes.
2. Push a closure-based processor that adds the user's identity to every subsequent log record's `extra` array.
3. Attach the configured `LoggerInterface` to the request via `withAttribute()`, making it available to downstream handlers.

---

### 3.8 Processors (`src/Processor/`)

| Class | Monolog Integration | Function |
|---|---|---|
| `RamseyUuidProcessor` | `ProcessorInterface` | Generates a UUID v7 (time-ordered) using the record's `datetime` and stores it in `extra['uuid']` |
| `LaminasI18nProcessor` | `ProcessorInterface` + `TranslatorAwareInterface` | Translates the log message using `Laminas\I18n\Translator`; only registered when `TranslatorInterface` is in container |

---

## 4. Architectural Layers and Dependencies

```
┌────────────────────────────────────────────────┐
│  Framework Integration Layer                   │
│  ConfigProvider, Factories, Delegator          │
│  (depends on: PSR-11, Laminas ServiceManager)  │
├────────────────────────────────────────────────┤
│  Middleware / Listener Layer                   │
│  MonologMiddleware, Psr3LogLaminasListener,    │
│  MezzioErrorListener                           │
│  (depends on: PSR-15, Laminas EventManager,    │
│   Monolog Logger)                              │
├────────────────────────────────────────────────┤
│  Core Logging Layer                            │
│  Monolog\Logger + Processors + Handlers        │
│  (depends on: monolog/monolog, ramsey/uuid,    │
│   laminas-db | webware/phpdb)                  │
├────────────────────────────────────────────────┤
│  Domain Primitives                             │
│  LogChannel (enum), Runtime (enum),            │
│  LogEvent                                      │
│  (no external dependencies)                   │
└────────────────────────────────────────────────┘
```

### Dependency Rules

- Domain primitives have **zero** external dependencies.
- The Core Logging Layer depends only on Monolog and the chosen DB abstraction.
- The Middleware / Listener Layer depends on PSR interfaces and Monolog; it never touches the DB layer directly.
- The Framework Integration Layer is the only layer allowed to reference the PSR-11 container.
- There are **no circular dependencies** within the component.

### Dependency Injection Pattern

All dependencies are injected via constructor injection. No class uses `new` internally for services (except `LogFactory`, which is the composition root for the logger). Service location is limited to factories.

---

## 5. Data Architecture

### Log Table Schema (MySQL)

```sql
CREATE TABLE `log` (
  `id`             int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`           char(36)     DEFAULT NULL,
  `channel`        varchar(255) NOT NULL,
  `level`          varchar(9)   NOT NULL,
  `userIdentifier` varchar(320) DEFAULT NULL,  -- LaminasDbHandler
  `user_identifier`varchar(320) DEFAULT NULL,  -- PhpDbHandler (snake_case)
  `message`        longtext     NOT NULL,
  `time`           int UNSIGNED NOT NULL,
  `context`        JSON         DEFAULT NULL,  -- PhpDbHandler only
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `ChannelIndex` (`channel`)
);
```

> **Note:** The `LaminasDbHandler` and `PhpDbHandler` use different column name conventions (`userIdentifier` vs `user_identifier`) and the `context` column is only written by `PhpDbHandler`. Ensure your schema matches the chosen handler.

### Data Flow: Record to Persistence

1. `Monolog\Logger` builds a `LogRecord` value object (immutable).
2. Processors mutate the record (via `$record->with(...)`) to enrich `extra`.
3. The handler receives the fully-processed `LogRecord` and maps its properties to an INSERT statement.
4. The record's `datetime` is stored as a UNIX timestamp integer (`format('U')`).
5. `context` + filtered `extra` are JSON-encoded together when using `PhpDbHandler`.

### UUID Strategy

UUID v7 (time-ordered, monotonic) is used — generated per record using `ramsey/uuid` with the record's own `datetime`. This allows chronological sorting on the `uuid` column while maintaining global uniqueness.

---

## 6. Cross-Cutting Concerns

### 6.1 Error Handling

- **Mezzio pipeline errors:** The `MezzioErrorHandlerDelegator` and `MezzioErrorListener` provide automatic error logging without any application-level code changes.
- **Handler errors:** Failures in `write()` propagate as exceptions. Monolog's `bubble` flag (default `true`) allows errors to continue up the handler stack.
- **Missing dependencies:** `LaminasI18nProcessorFactory` explicitly throws `ServiceNotFoundException` when the translator is absent, failing fast at container build time.

### 6.2 Authentication / User Identity

- `MonologMiddleware` reads `UserInterface` from the PSR-7 request attribute (Mezzio Authentication).
- The user identifier key (`email` by default) is configurable via `authentication.username` in the application config, supporting `mezzio-authentication-session` and other providers.

### 6.3 Internationalization

- `LaminasI18nProcessor` optionally translates log messages before they are written. It is only registered when `TranslatorInterface` is available in the container (checked in `LogFactory`).
- The `process_translation` config flag exists as a semantic marker but the actual guard is the container presence check.

### 6.4 Configuration Management

- All configuration is accessed via the PSR-11 container key `'config'`.
- Component-local config is namespaced under `ConfigProvider::class` (the FQCN).
- External config (authentication, DB adapter) is accessed via well-known conventional keys (`authentication.username`, `AdapterInterface::class`).

### 6.5 Logging (Meta)

The component itself does not log its own operations. Internal errors propagate as PHP exceptions.

---

## 7. Technology-Specific Patterns

### 7.1 Laminas Component Model

- `ConfigProvider` is the entry point for `laminas-component-installer`.
- The `extra.laminas` key in `composer.json` identifies the component and its config-provider FQCN.
- The component registers itself as a **library** (not an application) — it ships defaults that applications can override.

### 7.2 Mezzio Middleware Pipeline

- `MonologMiddleware` must be placed early in the pipeline (before authentication middleware, or immediately after) so that user identity is available for all downstream log calls.
- The component ships a commented-out `middleware_pipeline` section in `ConfigProvider::getPipelineConfig()` as a reference for application integration.

### 7.3 Laminas EventManager Bridge

- `Psr3LogLaminasListener` uses the **shared** event manager to listen across all components that trigger events under the `AbstractController`, `MiddlewareInterface`, or `RequestHandlerInterface` identifiers.
- Applications dispatch a `LogEvent` on their own `EventManager`; the shared manager propagates it to the listener.

### 7.4 Monolog Pipeline

Handler and processor registration order in `LogFactory`:
```
LaminasDbHandler        ← handler (persists to DB)
RamseyUuidProcessor     ← processor #1 (UUID v7)
PsrLogMessageProcessor  ← processor #2 (interpolate placeholders)
LaminasI18nProcessor    ← processor #3 (translate, optional)
```
Monolog processes records in **LIFO** order for processors and passes through handler stack in registration order.

### 7.5 PhpDbHandler vs LaminasDbHandler

| Feature | `LaminasDbHandler` | `PhpDbHandler` |
|---|---|---|
| DB abstraction | `laminas/laminas-db` | `webware/phpdb` |
| Column: user id | `userIdentifier` (camelCase) | `user_identifier` (snake_case) |
| Context storage | Not stored | JSON in `context` column |
| Extra filtering | Passes all extra to `uuid` and auth fields | Filters out `uuid` and auth key before JSON encoding |
| Constructor | Inline `Sql` instantiation in `write()` | `Sql` instance created once in constructor |
| `parent::__construct()` | Not called | Called with no args |

The `PhpDbHandler` is the current development focus (branch `add-phpdb-handler`).

---

## 8. Implementation Patterns

### 8.1 Adding a New Log Handler

1. Create `src/Handler/MyHandler.php` extending `Monolog\Handler\AbstractProcessingHandler`.
2. Implement `protected function write(LogRecord $record): void`.
3. Create `src/Handler/MyHandlerFactory.php` implementing `__invoke(ContainerInterface): MyHandler`.
4. Register in `ConfigProvider::getDependencies()`:
   ```php
   'factories' => [
       Handler\MyHandler::class => Handler\MyHandlerFactory::class,
   ],
   ```
5. Push the handler in `LogFactory::__invoke()`:
   ```php
   $logger->pushHandler($container->get(Handler\MyHandler::class));
   ```

### 8.2 Adding a New Processor

1. Create `src/Processor/MyProcessor.php` implementing `Monolog\Processor\ProcessorInterface`.
2. `__invoke(LogRecord $record): LogRecord` — enrich `$record->extra` and return `$record->with(extra: ...)`.
3. If container dependencies are needed, create `src/Processor/MyProcessorFactory.php`.
4. Register as `invokables` (no deps) or `factories` (with deps) in `ConfigProvider`.
5. Add `$logger->pushProcessor(...)` in `LogFactory`.

### 8.3 Adding a New Log Channel

Add a case to `LogChannel` enum:
```php
case MyChannel = 'my-channel';
```
Switch the logger's channel at the call-site via:
```php
$logger->withName(LogChannel::MyChannel->value)->info('...');
```
`withName()` creates a clone of the logger with a different channel name.

### 8.4 Triggering a Log via EventManager

```php
use Axleus\Log\Event\LogEvent;
use Monolog\Level;

$event = new LogEvent(Level::Info);
$event->setMessage('User {name} logged in');
$event->setContext(['name' => $username]);
$event->setChannel(LogChannel::User);
$this->getEventManager()->trigger($event);
```

---

## 9. Testing Architecture

### Structure

```
test/
├── unit/
│   └── TestAsset/          (shared test doubles — currently empty)
└── integration/
    ├── Extension/
    │   ├── ListenerExtension.php         (PHPUnit bootstrap extension)
    │   ├── IntegrationTestStartedListener.php
    │   └── IntegrationTestStoppedListener.php
    ├── Platform/
    │   ├── FixtureLoader.php             (interface: createDatabase / dropDatabase)
    │   └── MysqlFixtureLoader.php        (PDO-based MySQL fixture loader)
    └── TestFixtures/
        └── mysql.sql                     (DDL for the log table)
```

### Test Strategies

| Suite | Runner config | Purpose |
|---|---|---|
| `unit test` | `./test/unit` | Pure unit tests; no DB or network required |
| `integration test` | `./test/integration` | Full-stack DB tests against a live MySQL container |

### Integration Test Bootstrap

1. `ListenerExtension` registers `IntegrationTestStartedListener` and `IntegrationTestStoppedListener` as PHPUnit event subscribers.
2. On test start: `MysqlFixtureLoader::createDatabase()` runs `mysql.sql` to create the `log` table.
3. On test stop: `MysqlFixtureLoader::dropDatabase()` drops the database.
4. Connection parameters are supplied via environment variables (`TESTS_LAMINAS_DB_MYSQL_ADAPTER_*`).

### Quality Gates

PHPUnit is configured to **fail** on deprecations, notices, and warnings (`failOnDeprecation`, `failOnNotice`, `failOnWarning`), enforcing clean PHP 8.4+ code.

---

## 10. Deployment Architecture

### Docker Development Environment

```yaml
services:
  php:   # PHP 8.3+ container (configurable via PHP_VERSION env)
    volumes:
      - ./:/var/www/html
  mysql: # MySQL 8.0+ container (configurable via MYSQL_VERSION env)
    ports:
      - "3306:3306"
    volumes:
      - ./test/integration/TestFixtures/mysql.sql:/docker-entrypoint-initdb.d/mysql.sql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
```

The `mysql.sql` fixture is auto-executed by MySQL's `docker-entrypoint-initdb.d` mechanism, so the schema is ready immediately when the container starts.

### Environment Variables

| Variable | Default | Purpose |
|---|---|---|
| `PHP_VERSION` | `8.3.19` | PHP container image version |
| `MYSQL_VERSION` | `8.0.41` | MySQL container image version |
| `MYSQL_DATABASE` | `laminasdb_test` | Test database name |
| `MYSQL_USER` | `user` | MySQL user |
| `MYSQL_PASSWORD` | `password` | MySQL password |
| `MYSQL_RANDOM_ROOT_PASSWORD` | `yes` | Avoids hardcoded root password |
| `TESTS_LAMINAS_DB_MYSQL_ADAPTER_*` | (see phpunit.xml.dist) | Test DSN configuration |

### Runtime Dependency: DB Adapter

This component requires a `Laminas\Db\Adapter\AdapterInterface` (for `LaminasDbHandler`) or `PhpDb\Adapter\AdapterInterface` (for `PhpDbHandler`) to be registered in the application container. These are not shipped with this component — the host application is responsible for configuring the DB adapter.

---

## 11. Extension and Evolution Patterns

### 11.1 Switching from LaminasDbHandler to PhpDbHandler

1. Remove `LaminasDbHandler` from `LogFactory` handler registration.
2. Register `PhpDbHandler` in its place.
3. Update the DB schema to use `user_identifier` (snake_case) and add a `context JSON` column.
4. Ensure the `webware/phpdb` adapter is available in the application container.

### 11.2 Supporting Non-Mezzio Runtimes

The `Runtime::Mvc` case is defined but not yet used in conditional wiring. To enable Laminas MVC support:

1. Check `$config['log_runtime']` in `ConfigProvider` or relevant factories.
2. Use MVC-specific delegation patterns (e.g., `MvcEvent` listeners) where Mezzio middleware hooks are currently used.

### 11.3 Adding a Non-DB Handler

Any Monolog handler (e.g., file, Slack, Sentry) can be added by:
1. Registering it as a service in the DI container.
2. Pushing it onto the logger in `LogFactory`.

The handler stack in Monolog is ordered — add higher-priority handlers last (they are tried first).

### 11.4 Scoping Processors per Channel

The current `withName()` pattern clones the logger. If per-channel processors are needed, separate `Logger` instances (one per channel) should be maintained in a named service map rather than relying on `withName()`.

---

## 12. Architectural Decision Records

### ADR-001: PSR-3 as the Public API

**Context:** Multiple logging backends exist (Monolog, Laminas\Log, etc.)  
**Decision:** Expose only `Psr\Log\LoggerInterface` from the container; `Monolog\Logger` is an implementation detail.  
**Consequences:** Consumers are decoupled from Monolog. Swapping backends is possible without changing call-sites.

### ADR-002: Laminas EventManager Bridge via `Psr3LogLaminasListener`

**Context:** Laminas MVC controllers and components dispatch events via `EventManager`; Mezzio request handlers use PSR-3 directly.  
**Decision:** Provide `Psr3LogLaminasListener` to bridge the Laminas event bus to PSR-3.  
**Consequences:** Any Laminas MVC component can log without a PSR-3 dependency by dispatching a `LogEvent`. Adds complexity for Mezzio-only applications.

### ADR-003: UUID v7 for Record Identification

**Context:** Log records need unique, time-sortable identifiers.  
**Decision:** Use Ramsey UUID v7 (time-ordered), seeded with the record's `datetime`.  
**Consequences:** UUIDs are monotonically increasing within a second, enabling B-tree index efficiency on the `uuid` column. Requires `ramsey/uuid ^4.7`.

### ADR-004: Component Config Namespaced by FQCN

**Context:** Multiple Laminas components merge their configs into a single global `config` array.  
**Decision:** Store all component configuration under `ConfigProvider::class` as the array key.  
**Consequences:** Zero risk of config key collisions with other packages. Slightly verbose to access — requires `$config[ConfigProvider::class]['key']`.

### ADR-005: Dual DB Handler Strategy

**Context:** The original `LaminasDbHandler` uses `laminas-db`; `webware/phpdb` is a newer, more lightweight adapter also used across the Axleus ecosystem.  
**Decision:** Provide both handlers; applications choose which to wire in `LogFactory`.  
**Consequences:** Slight code duplication between the two handlers. The `PhpDbHandler` is more feature-complete (stores context JSON, uses snake_case column names). Future consolidation should standardize on one handler.

---

## 13. Architecture Governance

### Static Analysis

- **Psalm** (`vimeo/psalm`) with `--shepherd` and `--stats` flags; baseline tracked in `psalm-baseline.xml`.
- **PHPStan** (`phpstan/phpstan` + `phpstan-phpunit`) at strict levels.

### Coding Standards

- **PHP_CodeSniffer** with the `webware/coding-standard` ruleset (`phpcs.xml`).
- Auto-fixable violations: `phpcbf` (composer `cs-fix` script).

### CI Quality Gates (`composer check`)

```
cs-check → cs-fix (phpcs)
static-analysis (psalm)
test (phpunit unit suite)
```

### Security

- `roave/security-advisories` (dev dependency) prevents installation of packages with known CVEs.
- `renovate.json` is present, indicating automated dependency update PRs via Renovate Bot.

### Branch Strategy

| Branch | Purpose |
|---|---|
| `0.0.x` | Default / stable release branch |
| `add-phpdb-handler` | Active development — adds `PhpDbHandler` |

### Known Technical Debt

| Location | Issue |
|---|---|
| `LaminasDbHandler` | `// todo: refactor this to use the adapter and a insert instance` — `Sql` is re-created per `write()` call |
| `MonologMiddleware` | `// todo: abstract this to detect which config is being used` — hard-coded `UserInterface` attribute key |
| `LogEvent::getChannel()` | `// todo improve this method` — instantiates `new ConfigProvider()` to read default channel |
| `LogFactory` | `process_uuid` and `process_translation` config flags exist but UUID processor is always pushed |
| `phpunit.xml.dist` | Schema reference targets PHPUnit 11.4 but `composer.json` requires PHPUnit 13 |
