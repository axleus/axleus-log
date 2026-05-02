# Session Context — axleus/axleus-log refactor to 0.1.0

**Last updated:** 2026-05-02  
**Branch:** `0.1.x`  
**Plan file:** `plan/refactor-axleus-log-0.1.0.md`  
**Blueprint:** `docs/Project_Architecture_Blueprint.md`

---

## Where We Left Off

Phases 1, 2, and 3 are **complete**. Phase 3 was the last active work.  
The test suite could not be run locally because WAMP has PHP `8.4.0` and PHPUnit requires `>=8.4.1`.  
**Tests must be run via Docker** (`compose.yml` is present in the project root).

---

## Completed Work

### Phase 1 — Config key migration ✅
All factories and delegators now read `$config[LoggerInterface::class]` instead of `$config[ConfigProvider::class]`.

### Phase 2 — Laminas MVC removal ✅
- `src/Runtime.php` deleted  
- `Psr3LogLaminasListener` stripped of `AbstractController` identifier; marked `@deprecated since 0.1.0`  
- `log_runtime` config key removed  
- Psalm / PHP_CodeSniffer configs deleted; PHPStan + php-cs-fixer are the sole toolchain

### Phase 3 — PSR-14 event dispatcher support ✅

**New / changed files:**

| File | Status | Notes |
|------|--------|-------|
| `src/Event/LogEvent.php` | Rewritten | Implements `StoppableEventInterface`; plain typed properties; no Laminas EM dependency |
| `src/Listener/Psr3LogPsr14Listener.php` | New | Callable PSR-14 listener; bridges `LogEvent` → PSR-3 `LoggerInterface` |
| `src/Listener/Psr3LogPsr14ListenerFactory.php` | New | Resolves `LoggerInterface` from container |
| `src/Container/ListenerProviderAggregateFactory.php` | New | Builds `ListenerProviderAggregate` from `'listeners'` + `'listener_providers'` config; mirrors `webware/commandbus-event` pattern |
| `src/ConfigProvider.php` | Updated | PSR-14 aliases, factory + invokable wiring, `LISTENER_KEY` / `LISTENER_PROVIDER_KEY` constants, `getListeners()` now returns keyed PSR-14 format |
| `test/unit/ConfigProviderTest.php` | Updated | Added assertions for LISTENER_KEY, LISTENER_PROVIDER_KEY, PSR-14 aliases, and `getListeners()` format |

**Key design decisions:**
- `ConfigProvider::LISTENER_KEY = 'listeners'` and `LISTENER_PROVIDER_KEY = 'listener_providers'` — matches `webware/commandbus-event` config conventions exactly
- `getListeners()` format: `[LogEvent::class => [['listener' => Psr3LogPsr14Listener::class, 'priority' => 1]]]`
- `ListenerProviderAggregateFactory` routes entries with `'priority'` key to `PrioritizedListenerProvider`; plain callables to `AttachableListenerProvider`; host-provided providers (from `'listener_providers'`) are attached first
- DI aliases: `EventDispatcherInterface::class → EventDispatcher::class`, `ListenerProviderInterface::class → ListenerProviderAggregate::class`
- DI invokables: `AttachableListenerProvider::class`, `PrioritizedListenerProvider::class` (phly's built-ins are constructorless)
- `Psr3LogLaminasListener` remains in `factories` (deprecated; host app wires it via Laminas SharedEventManager manually)
- `phly/phly-event-dispatcher` is in `require` (not `require-dev`)

---

## Remaining Work

### Phase 4 — Dual DB adapter resolution (TASK-021 to 024)

| Task | Description |
|------|-------------|
| TASK-021 | `LaminasDbHandlerFactory`: resolve adapter as `\Laminas\Db\Adapter\AdapterInterface::class`; add inline comment |
| TASK-022 | `PhpDbHandlerFactory`: resolve adapter as `\PhpDb\Adapter\AdapterInterface::class`; add inline comment about phpdb config structure difference |
| TASK-023 | `LaminasDbHandlerFactory`: replace any ambiguous `AdapterInterface::class` with the fully-qualified Laminas FQCN |
| TASK-024 | Add "DB Adapter Configuration" section to README explaining both adapters, phpdb config differences, and handler selection in `LogFactory` |

### Phase 5 — Technical debt (TASK-025 to 030)

| Task | Description |
|------|-------------|
| TASK-025 | `LaminasDbHandler`: move `new Sql(...)` from `write()` to constructor; declare `private readonly Sql $sql` |
| TASK-026 | `LaminasDbHandler`: rename column `userIdentifier` → `user_identifier`; update `test/integration/TestFixtures/mysql.sql`; document breaking schema change in CHANGELOG |
| TASK-027 | `MonologMiddleware`: read auth attribute key from `$config[LoggerInterface::class]['auth_attribute']` with fallback to `Mezzio\Authentication\UserInterface::class`; inject config via constructor; update factory |
| TASK-028 | `LogFactory`: wrap `pushProcessor($uuidProcessor)` in `if ($config['process_uuid'])` guard; wrap `LaminasI18nProcessor` push in `if ($config['process_translation'])` guard |
| TASK-029 | `phpunit.xml.dist`: update schema URL from `11.4` to `13.0` |
| TASK-030 | `ConfigProvider::getConfigDefaults()`: confirm `process_uuid` and `process_translation` defaults are already present (they are — no action needed, mark ✅) |

### Tests still to write

- `test/unit/LogEventTest.php` — full PSR-14 value object coverage (propagation stopped, accessors, all `LogChannel` enum values)
- `test/unit/Listener/Psr3LogPsr14ListenerTest.php` — verify `__invoke` calls `$logger->log()` correctly; verify `withName()` is called for non-App channels
- Integration tests (Phase 4/5 prerequisite): `LaminasDbHandler` schema must be updated before integration tests can run cleanly

---

## Environment Notes

- **PHP version blocker**: WAMP has PHP `8.4.0`; PHPUnit requires `>=8.4.1`. Run tests with:
  ```
  docker compose run --rm php vendor/bin/phpunit --testdox
  ```
- **Code style**: `composer cs-fix` (runs `php-cs-fixer fix`)
- **Static analysis**: `composer static-analysis` (runs `phpstan analyse`)
- **composer.json scripts**: `cs-check`, `cs-fix`, `static-analysis` are all wired correctly

---

## Files of Interest (Quick Jump)

- [src/ConfigProvider.php](../src/ConfigProvider.php)
- [src/Container/ListenerProviderAggregateFactory.php](../src/Container/ListenerProviderAggregateFactory.php)
- [src/Event/LogEvent.php](../src/Event/LogEvent.php)
- [src/Listener/Psr3LogPsr14Listener.php](../src/Listener/Psr3LogPsr14Listener.php)
- [src/Listener/Psr3LogPsr14ListenerFactory.php](../src/Listener/Psr3LogPsr14ListenerFactory.php)
- [src/Listener/Psr3LogLaminasListener.php](../src/Listener/Psr3LogLaminasListener.php) ← deprecated
- [test/unit/ConfigProviderTest.php](../test/unit/ConfigProviderTest.php)
- [plan/refactor-axleus-log-0.1.0.md](../plan/refactor-axleus-log-0.1.0.md)
