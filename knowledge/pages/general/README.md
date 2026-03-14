<!--
id: readme
tags: ''
-->

# Test Fixture Framework

![main](../../images/hero.jpg)

A lightweight PHP framework for fixture management to be used with any testing framework.

When you need to do some setup (create users, content, etc) before running tests, call on this project.

Each fixture class (`implements \AKlump\TestFixture\FixtureInterface`) should be responsible for a single test fixture, such as creating a user.

## Key Features

- **Attribute-based Metadata**: No docblock parsing; uses `#[Fixture]` for configuration.
- **Composer Discovery**: Scans `vendor/` and project directories using Composer's autoload metadata.
- **Deterministic Ordering**: Supports weights and `after`/`before` dependencies with topological sorting.
- **Cached Discovery**: Fast execution via JSON indexing.
- **Global Options**: Pass a shared options array to every fixture.

{{ composer.install|raw }}

## Quick Start

We assume a tests directory called `e2e/`

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

3. Create your first fixture `class MyApp\Tests\Fixture\SomeFixture extends \AKlump\TestFixture\AbstractFixture`
4. Be sure to use `#[Fixture(id: 'some_fixture')]`
5. `mkdir bin/`
6. Create (see below) the runner `bin/setup-fixtures`, changing `'MyApp\Tests\Fixture',` to the appropriate namespace.
8. Execute while (**flushing the cache** after creating new classes), e.g. `bin/setup-fixtures --flush`

## Core Components

### 1. `FixtureInterface`

Every fixture must implement this interface:

```php
namespace AKlump\TestFixture;

interface FixtureInterface {
  public function setUp(array $options): void;
  public function onSuccess(bool $silent = FALSE);
  public function onFailure(FixtureException $e, bool $silent = FALSE);
}
```

### 2. `#[Fixture]` Attribute

Used to define fixture metadata:

- `id` (string, required): Unique identifier.
- `weight` (int, default 0): Lower weights run earlier.
- `after` (array, optional): IDs of fixtures that must run before this one.
- `before` (array, optional): IDs of fixtures that must run after this one.
- `tags` (array, optional): Metadata for future filtering.
- `discoverable` (bool, default true): Set to `false` to hide from discovery (e.g., test-only fixtures).

**Example:**

```php
use AKlump\TestFixture\FixtureInterface;
use AKlump\TestFixture\Fixture;

#[Fixture(id: 'user_roles', weight: -10, after: ['base_schema'])]
class UserRolesFixture implements FixtureInterface {
  // ...
}
```

### 3. Accessing Metadata via `FixtureMetadataTrait`

If you want your fixture to have access to its own metadata (e.g., to get the `id` or `tags` defined in the attribute), you can use the `FixtureMetadataTrait`.

This trait adds a public `array $fixture` property to your class. The `FixtureRunner` detects this property and populates it with the fixture's metadata record before calling `setUp()`.

```php
use AKlump\TestFixture\FixtureInterface;
use AKlump\TestFixture\Fixture;
use AKlump\TestFixture\FixtureMetadataTrait;

#[Fixture(id: 'user_roles')]
class UserRolesFixture implements FixtureInterface {

  use FixtureMetadataTrait;

  public function setUp(array $options): void {
    $id = $this->fixture['id'];
    // ...
  }
}
```

### 4. `AbstractFixture` Class

The `AbstractFixture` class provides a base implementation of `FixtureInterface` and includes the `FixtureMetadataTrait`. Extending this class simplifies fixture development and allows for custom success/failure handling.

```php
use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Fixture;

#[Fixture(id: 'user_roles')]
class UserRolesFixture extends AbstractFixture {

  public function setUp(array $options): void {
    $id = $this->fixture['id'];
    // ...
  }

}
```

#### Customizing Success and Failure

You can override `onSuccess` and `onFailure` to provide custom feedback, such as using emojis for better visibility.

```php
use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Fixture;
use AKlump\TestFixture\Exception\FixtureException;

#[Fixture(id: 'custom_output')]
class CustomOutputFixture extends AbstractFixture {

  public function setUp(array $options): void {
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
    // Don't forget to re-throw if you want the runner to catch it!
    throw $e;
  }

}
```

## Presumptions

1. One fixture represents a single atomic test-required element (user, piece of content, page, etc).

## Discovery and Execution

### Discovery

`FixtureDiscovery` uses `vendor/composer/autoload_psr4.php` and `vendor/composer/autoload_classmap.php` to find classes implementing `FixtureInterface` with the `#[Fixture]` attribute. You may hide a fixture by setting `discoverable: TRUE` in the `#[Fixture]` attribute.

### Ordering

`FixtureOrderer` performs a topological sort based on `after`/`before` rules, tie-breaking with `weight` and lexicographical `id`.

### Runner `setup-fixures`

To run all your fixtures create a script (`test_fixtures`) that does something like this:

```php
#!/usr/bin/env php
<?php
{{ run_fixtures_php|raw }}
```

## Cache Management

`FixtureCache` stores discovered metadata. It invalidates automatically if Composer's autoload files change. Use the `$rebuild_cache` parameter on `\AKlump\TestFixture\Helper\GetFixtures` to force discovery.
