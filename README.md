# Fixture Framework

![main](images/hero.jpg)

A lightweight PHP framework for fixture management that prepares application state for repeatable workflows such as testing, local development, and environment-specific setup.

A fixture is a discoverable, ordered unit of setup logic. Use fixtures whenever you need to create, sanitize, enable, normalize, or otherwise prepare your application into a known state.

Each fixture class (`implements \AKlump\FixtureFramework\FixtureInterface`) should be responsible for a single atomic concern, such as creating a user, sanitizing imported data, or enabling development modules.

## Key Features

- **Attribute-based Metadata**: No docblock parsing; uses `#[Fixture]` for configuration.
- **Composer Discovery**: Scans `vendor/` and project directories using Composer's autoload metadata.
- **Deterministic Ordering**: Supports weights and `after`/`before` dependencies with topological sorting.
- **Cached Discovery**: Fast execution via JSON indexing.
- **Global Options**: Pass a shared options array to every fixture.

## Install with Composer

1. Because this is an unpublished package, you must define it's repository in
   your project's _composer.json_ file. Add the following to _composer.json_ in
   the `repositories` array:
   
    ```json
    {
     "type": "github",
     "url": "https://github.com/aklump/fixture-framework"
    }
    ```
1. Require this package:
   
    ```
    composer require --dev aklump/fixture-framework:^0.0
    ```

## Quick Start

This example uses a test-oriented directory such as `e2e/`, but fixtures can be used for any repeatable application setup workflow, including local development preparation and post-import sanitization.

1. `mkdir e2e/src/Fixture/`
2. Map it to a namespace in `composer.json`

```json
{
  "autoload-dev": {
    "psr-4": {n
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
6. Create the runner `bin/setup-fixtures`, changing `'MyApp\\Tests\\Fixture'` to the appropriate namespace.
7. Run it for your workflow, for example to prepare local state, sanitize imported content, or set up test prerequisites.
8. Flush the cache after creating new classes, e.g. `bin/setup-fixtures --flush`

## Core Components

### 1. `FixtureInterface`

Every fixture must implement this interface:

```php
namespace AKlump\FixtureFramework;

interface FixtureInterface {
public function __invoke(): void;
public function onSuccess(bool $silent = FALSE);
public function onFailure(FixtureException $e, bool $silent = FALSE);
}
```

### 2. `#[Fixture]` Attribute

Used to define fixture metadata.

A fixture is not limited to test data. It may prepare any repeatable application state, such as baseline content, sanitized users, enabled development modules, or other context-specific setup.

- `id` (string, required): Unique identifier.
- `weight` (int, default 0): Lower weights run earlier.
- `after` (array, optional): IDs of fixtures that must run before this one.
- `before` (array, optional): IDs of fixtures that must run after this one.
- `tags` (array, optional): Metadata for future filtering.
- `discoverable` (bool, default true): Set to `false` to hide from discovery.

**Example:**

```php
use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'user_roles', weight: -10, after: ['base_schema'])]
class UserRolesFixture implements FixtureInterface {
// ...
}
```

### 3. Accessing Metadata via `FixtureMetadataTrait`

If you want your fixture to have access to its own metadata (for example, to get the `id` or `tags` defined in the attribute), you can use the `FixtureMetadataTrait`.

This trait adds a public `array $fixture` property to your class. The `FixtureRunner` detects this property and populates it with the fixture's metadata record before calling `__invoke()`.

```php
use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\Traits\FixtureMetadataTrait;

#[Fixture(id: 'user_roles')]
class UserRolesFixture implements FixtureInterface {

use FixtureMetadataTrait;

public function __invoke(): void {
$id = $this->fixture['id'];
// ...
}
}
```

### 4. `AbstractFixture` Class

The `AbstractFixture` class provides a base implementation of `FixtureInterface` and includes the `FixtureMetadataTrait`, `FixtureRunContextTrait`, and `FixtureOptionsTrait`. Extending this class simplifies fixture development and allows for custom success/failure handling.

```php
use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'user_roles')]
class UserRolesFixture extends AbstractFixture {

public function __invoke(): void {
$id = $this->fixture['id'];
// ...
}

}
```

#### Injected Properties

When using `AbstractFixture`, or the respective traits, the following properties are automatically injected into the fixture instance by the `FixtureRunner`:

- `$this->fixture`: (array) Contains the fixture's metadata (id, weight, tags, etc.).
- `$this->runContext`: (`\AKlump\FixtureFramework\RunContext`) A shared mutable runtime output across all fixtures in a single run.
- `$this->options`: (`\AKlump\FixtureFramework\RunOptions`) A read-only API for the global run options.

#### Global Run Options

Run options are provided to the `FixtureRunner` as an array or a `RunOptions` object. Inside a fixture, you can access them via `$this->options`.

**Important:** Run options must only contain plain data (null, scalars, or arrays of the same). Objects, closures, and resources are not allowed.

```php
public function __invoke(): void {
$env = $this->options->get('env');
$url = $this->options->require('base_url');
$all = $this->options->all();
}
```

- `RunOptions` = read-only run input.
- `RunContext` = shared mutable runtime output.

#### Customizing Success and Failure

You can override `onSuccess` and `onFailure` to provide custom feedback.

```php
use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\Exception\FixtureException;

#[Fixture(id: 'custom_output')]
class CustomOutputFixture extends AbstractFixture {

  public function __invoke(): void {
    // ...
  }

  public function onSuccess(bool $silent = FALSE) {
    if (!$silent) {
      echo "✅ Successfully completed!" . PHP_EOL;
    }
  }

  public function onFailure(FixtureException $e, bool $silent = FALSE) {
    if (!$silent) {
      echo "❌ Failed: " . $e->getMessage() . PHP_EOL;
    }
    throw $e;
  }

}
```

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

### Runner `setup-fixtures`

To run your fixtures, create a script such as `bin/setup-fixtures` that bootstraps your application and executes the discovered fixtures for the desired workflow.

```php
#!/usr/bin/env php
<?php
{{ setup-fixtures|raw }}
```

## Cache Management

`FixtureCache` stores discovered metadata. It invalidates automatically if Composer's autoload files change. Use the `$rebuild_cache` parameter on `\AKlump\FixtureFramework\Helper\GetFixtures` to force discovery.
