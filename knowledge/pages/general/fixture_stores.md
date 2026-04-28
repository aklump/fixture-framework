<!--
id: fixture_stores
tags: ''
-->

# Fixture Stores

The Fixture Framework uses storage classes to persist the `RunContext` across different stages of a fixture run. All storage classes must implement the `\AKlump\FixtureFramework\Runtime\RunContextStoreInterface`.

## RunContextStoreInterface

This interface defines the methods required for any run context storage implementation.

- `set(string $key, mixed $value): void`: Stores a value with a given key. Throws `\AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException` if the key is already set.
- `get(string $key, mixed $default = null): mixed`: Retrieves a value by key, or returns the default if not found.
- `has(string $key): bool`: Checks if a key exists in the store.
- `all(): array`: Retrieves all stored key-value pairs.

## Available Implementations

### RunContextStore (In-Memory)

This is the default implementation. It stores data in a PHP array in memory. It is fast but non-persistent across separate PHP processes.

```php
use AKlump\FixtureFramework\Runtime\RunContextStore;

$store = new RunContextStore();
```

### RunContextStoreFile (File-Based)

This implementation persists the context to a serialized file. It is useful when you need to inspect the context after a run or if the run is split across multiple processes.

```php
use AKlump\FixtureFramework\Runtime\RunContextStoreFile;

$store = new RunContextStoreFile('/path/to/context.serialized');
```

### RunContextStoreSqLite (SQLite-Based)

This implementation uses an SQLite database for persistence. It is robust and handles large amounts of data efficiently.

```php
use AKlump\FixtureFramework\Runtime\RunContextStoreSqLite;

$db_path = '/path/to/database.sqlite';
$table_name = 'run_context'; // Optional, defaults to 'run_context'
$store = new RunContextStoreSqLite($db_path, $table_name);
```

## Usage

To use a specific store, pass it as the second argument to `FixtureCollectionBuilder::__invoke()`. This is typically done in your `setup-fixtures.php` script.

```php
use AKlump\FixtureFramework\Runtime\FixtureCollectionBuilder;
use AKlump\FixtureFramework\Runtime\RunContextStoreSqLite;

$store = new RunContextStoreSqLite(__DIR__ . '/cache/context.sqlite');
$builder = new FixtureCollectionBuilder();
$fixtures = $builder($discovery, $store);

// Now the runner will use the SQLite store for the RunContext.
$runner = new FixtureRunner($fixtures, $options);
$runner->run();
```

## Performance and Caching

All persistent implementations (`File` and `SqLite`) use internal memory caching. This ensures that:
- Multiple `get()` calls for the same key only involve a single I/O operation.
- `has()` calls populate the cache if the key exists.
- `all()` calls are served from memory if the entire store has already been loaded.

This makes it performant to use these stores even when they are persistent.

