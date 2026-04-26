<!--
id: readme
tags: ''
-->

# Fixture Framework

![main](../../images/hero.jpg)

A lightweight PHP framework for fixture management that prepares application state for repeatable workflows such as testing, local development, and environment-specific setup.

A fixture is a discoverable, ordered unit of setup logic. Use fixtures whenever you need to create, sanitize, enable, normalize, or otherwise prepare your application into a known state.

Each fixture class (usually `extends \AKlump\FixtureFramework\AbstractFixture`) should be responsible for a single atomic concern, such as creating a user, sanitizing imported data, or enabling development modules.

## Key Features

- **Attribute-based Metadata**: No docblock parsing; uses `#[Fixture]` for configuration.
- **Composer Discovery**: Scans `vendor/` and project directories using Composer's autoload metadata.
- **Deterministic Ordering**: Supports weights and `after`/`before` dependencies with topological sorting.
- **Cached Discovery**: Fast execution via JSON indexing.
- **Global Options**: Pass a shared options array to every fixture.

{{ composer.install|raw }}

## Quick Start

This example uses a test-oriented directory such as `e2e/`, but fixtures can be used for any repeatable application setup workflow, including local development preparation and post-import sanitization.

1. `mkdir e2e/src/Fixture/`
2. Map it to a namespace in `composer.json`

```json
{
  "autoload-dev": {
    "psr-4": {
      "MyApp\\Tests\\": [
        "e2e/src/"
      ]
    }
  }
}
```

3. Create your first fixture `class MyApp\Tests\Fixture\SomeFixture extends \AKlump\FixtureFramework\AbstractFixture`
4. Be sure to use `#[Fixture(id: 'some_fixture')]`
5. `mkdir bin/`
6. Create the runner `bin/setup-fixtures.php`, changing `'MyApp\\Tests\\Fixture'` to the appropriate namespace.
7. Run it for your workflow, for example to prepare local state, sanitize imported content, or set up test prerequisites.
8. Flush the cache after creating new classes, e.g. `bin/setup-fixtures.php --flush`

## Core Components

### 1. `FixtureInterface`

Every fixture must implement this interface:

{{ file.FixtureInterface_php|fenced }}

### 2. `#[Fixture]` Attribute

Used to identify classes as fixtures and to define fixture metadata.

- `id` (string, required): Unique identifier.
- `description` (string, optional): A short, plain-text summary of the fixture.
- `weight` (int, default 0): Lower weights run earlier.
- `after` (array, optional): IDs of fixtures that must run before this one.
- `before` (array, optional): IDs of fixtures that must run after this one.
- `tags` (array, optional): Metadata for future filtering.
- `discoverable` (bool, default true): Set to `false` to hide from discovery.

{{ snippet.fixture_attributes|fenced }}

#### Usage Guidance for `description`

The `description` field is intended for short, human-readable summaries suitable for compact display in CLI tables, catalogs, or reports. It should be a concise phrase or sentence.

Class docblocks remain the appropriate place for longer developer-facing documentation, implementation details, assumptions, or warnings.

```php
/**
 * Longer developer-facing explanation.
 *
 * May include implementation details, assumptions, examples, or warnings.
 */
#[Fixture(
  id: 'users',
  description: 'Creates baseline user records.',
  tags: ['users', 'seed'],
)]
class UserFixture extends AbstractFixture {
}
```

### 3. `AbstractFixture` Class

The `AbstractFixture` class provides a base implementation of `FixtureInterface` and includes the `FixtureMetadataTrait`, `FixtureRunContextTrait`, and `FixtureOptionsTrait`. Extending this class simplifies fixture development and allows for custom success/failure handling.

{{ snippet.ExampleFixture_php|fenced }}

#### Customizing Success and Failure

You can override `onSuccess` and `onFailure` to provide custom feedback.

{{ snippet.CustomOutputFixture_php|fenced }}

### 4. Injected Properties

When using `AbstractFixture`, or the respective traits, the following properties are automatically injected into the fixture instance by the `FixtureRunner`:

- `$this->fixture()` (or `$this->fixture` for BC): (array) Contains the fixture's metadata (id, weight, tags, etc.).
- `$this->context()` (or `$this->runContext` for BC): (`\AKlump\FixtureFramework\RunContext`) A shared mutable runtime output across all fixtures in a single run.
- `$this->options()` (or `$this->options` for BC): (`\AKlump\FixtureFramework\RunOptions`) A read-only API for the global run options.

#### Auto-wiring via Interfaces

Auto-wiring is triggered by the implementation of specific capability interfaces:

- `FixtureDefinitionAwareInterface`
- `RunOptionsAwareInterface`
- `RunContextAwareInterface`

The `AbstractFixture` class implements all of these interfaces. If you create a custom fixture that does not extend `AbstractFixture`, you must implement these interfaces to receive injection.

#### Accessing Metadata via `FixtureMetadataTrait`

If you want your fixture to have access to its own metadata (for example, to get the `id` or `tags` defined in the attribute), you can use the `FixtureMetadataTrait`.

{{ file.FixtureMetadataTrait_php|fenced }}

This trait adds a public `array $fixture` property to your class. The `FixtureRunner` detects the `FixtureDefinitionAwareInterface` (which `AbstractFixture` implements) and populates it with the fixture's metadata record before calling `__invoke()`.

{{ snippet.get_fixture_id|fenced }}

#### Run Context

The `RunContext` is a shared, mutable data store that persists throughout a single execution run. It is the primary mechanism for fixtures to communicate or pass data to downstream fixtures (e.g., passing a created user's ID to a subsequent profile-creation fixture).

- **Shared Mutable State**: Every fixture in the collection receives the same `RunContext` instance, allowing state to be built up across the entire run.
- **Strict Namespacing**: For data integrity, a fixture may only `set()` keys that are prefixed with its own fixture ID (e.g., `my_fixture.entity_id`). This prevents fixtures from accidentally overwriting each other's data.
- **Data Retrieval**: Use `get()` to retrieve values, `has()` to check for existence, or `require()` to throw an exception if a critical piece of shared data is missing.

{{ snippet.run_context_php|fenced }}

#### Global Run Options

Run options are provided to the `FixtureRunner` as an array or a `RunOptions` object. Inside a fixture, you can access them via `$this->options` which will always be an instance of `\AKlump\FixtureFramework\RunOptions`.

**Important:** Run options must only contain plain data (null, scalars, or arrays of the same). Objects, closures, and resources are not allowed unless you extend `\AKlump\FixtureFramework\Runtime\RunOptionsValidator` as show below.

{{ snippet.using_options|fenced }}

##### Allowing Custom Run Options

{{ snippet.RunOptionsValidator_php|fenced }}

## Design Principles

1. One fixture should represent a single atomic setup concern.
2. Fixtures should prepare a known, repeatable application state.
3. Fixtures may be used for testing, local development, imports, or other environment-specific workflows.
4. Ordering should express dependency, not implementation detail.

## Common Use Cases

Fixtures are useful anywhere your application needs repeatable state preparation, for example:

- creating prerequisite users, content, or configuration for tests
- sanitizing production-derived data for safe local development
- enabling development-only modules or features
- normalizing imported assets before local use
- preparing demo or QA environments with known content/state

## Discovery and Execution

### Discovery

`FixtureDiscovery` uses `vendor/composer/autoload_psr4.php` and `vendor/composer/autoload_classmap.php` to find classes implementing `FixtureInterface` with the `#[Fixture]` attribute. You may hide a fixture by setting `discoverable: false` in the `#[Fixture]` attribute.

### Ordering

`FixtureOrderer` performs a topological sort based on `after`/`before` rules, tie-breaking with `weight` and lexicographical `id`.

### Runner `setup-fixtures.php`

To run your fixtures, create a script such as `bin/setup-fixtures.php` that bootstraps your application and executes the discovered fixtures for the desired workflow.

{{ file.setup_fixtures_php|fenced }}

## Cache Management

`FixtureCache` stores discovered metadata. It invalidates automatically if Composer's autoload files change. Use the `$rebuild_cache` parameter on `\AKlump\FixtureFramework\Helper\GetFixtures` to force discovery.
