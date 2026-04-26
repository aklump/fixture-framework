# Fixture Framework

![main](images/hero.jpg)

A lightweight PHP framework for fixture management that prepares application state for repeatable workflows such as testing, local development, and environment-specific setup.

A fixture is a discoverable, ordered unit of setup logic. Use fixtures whenever you need to create, sanitize, enable, normalize, or otherwise prepare your application into a known state.

Each fixture class (usually `extends \AKlump\FixtureFramework\AbstractFixture`) should be responsible for a single atomic concern, such as creating a user, sanitizing imported data, or enabling development modules.

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

**File:** `FixtureInterface.php`

```php
<?php

namespace AKlump\FixtureFramework;

use AKlump\FixtureFramework\Exception\FixtureException;

interface FixtureInterface {

  /**
   * Returns the unique identifier for the fixture.
   *
   * @return string
   *   The fixture ID.
   */
  public function id(): string;

  /**
   * Returns a brief description of what the fixture does.
   *
   * The description should be a short, human-readable summary suitable for
   * compact display in CLI tables, catalogs, or reports (e.g., a concise phrase
   * or sentence). Longer developer-facing documentation, implementation
   * details, or warnings should remain in the class docblock.
   *
   * @return string
   *   The fixture description.
   */
  public function description(): string;

  /**
   * Executes the fixture logic.
   *
   * This is where the actual work of the fixture (e.g., seeding data)
   * should be implemented.
   *
   * @return void
   *
   * @throws \AKlump\FixtureFramework\Exception\FixtureException
   *   If the fixture fails to execute correctly.
   */
  public function __invoke(): void;

  /**
   * Callback executed when the fixture completes successfully.
   *
   * @param bool $silent
   *   If TRUE, the callback should not produce any output.
   *
   * @return mixed
   */
  public function onSuccess(bool $silent = FALSE);

  /**
   * Callback executed when the fixture throws a FixtureException.
   *
   * @param \AKlump\FixtureFramework\Exception\FixtureException $e
   *   The exception that was thrown.
   * @param bool $silent
   *   If TRUE, the callback should not produce any output.
   *
   * @return mixed
   */
  public function onFailure(FixtureException $e, bool $silent = FALSE);
}
```

### 2. `#[Fixture]` Attribute

Used to identify classes as fixtures and to define fixture metadata.

- `id` (string, required): Unique identifier.
- `description` (string, optional): A short, plain-text summary of the fixture.
- `weight` (int, default 0): Lower weights run earlier.
- `after` (array, optional): IDs of fixtures that must run before this one.
- `before` (array, optional): IDs of fixtures that must run after this one.
- `tags` (array, optional): Metadata for future filtering.
- `discoverable` (bool, default true): Set to `false` to hide from discovery.

```php
#[Fixture(
  id: 'example_fixture',
  description: 'An example fixture that demonstrates metadata.',
  weight: -10,
  after: ['base_schema']
)]
class ExampleFixture extends AbstractFixture {
```

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

**File:** `ExampleFixture.php`

```php
<?php

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(
  id: 'example_fixture',
  description: 'An example fixture that demonstrates metadata.',
  weight: -10,
  after: ['base_schema']
)]
class ExampleFixture extends AbstractFixture {

  public function __invoke(): void {
    $fixture_id = $this->fixture['id'];
  }
}
```

#### Customizing Success and Failure

You can override `onSuccess` and `onFailure` to provide custom feedback.

**File:** `CustomOutputFixture.php`

```php
<?php

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

**File:** `FixtureMetadataTrait.php`

```php
<?php

namespace AKlump\FixtureFramework\Traits;

/**
 * Trait to provide a common property for accessing fixture metadata.
 *
 * Use this trait in your Fixture classes to have the metadata automatically
 * populated by the FixtureRunner before the __invoke() method is called.
 */
trait FixtureMetadataTrait {

  /**
   * The fixture metadata record as discovered by FixtureDiscovery.
   *
   * @var array
   *
   * @deprecated Use fixture() instead, this property will be made private in a
   * future version.
   */
  public array $fixture;

  public function setFixtureDefinition(array $definition): void {
    $this->fixture = $definition;
  }

  public function fixture(): array {
    return $this->fixture;
  }

}
```

This trait adds a public `array $fixture` property to your class. The `FixtureRunner` detects the `FixtureDefinitionAwareInterface` (which `AbstractFixture` implements) and populates it with the fixture's metadata record before calling `__invoke()`.

```php
  public function __invoke(): void {
    $fixture_id = $this->fixture['id'];
```

#### Run Context

The `RunContext` is a shared, mutable data store that persists throughout a single execution run. It is the primary mechanism for fixtures to communicate or pass data to downstream fixtures (e.g., passing a created user's ID to a subsequent profile-creation fixture).

- **Shared Mutable State**: Every fixture in the collection receives the same `RunContext` instance, allowing state to be built up across the entire run.
- **Strict Namespacing**: For data integrity, a fixture may only `set()` keys that are prefixed with its own fixture ID (e.g., `my_fixture.entity_id`). This prevents fixtures from accidentally overwriting each other's data.
- **Data Retrieval**: Use `get()` to retrieve values, `has()` to check for existence, or `require()` to throw an exception if a critical piece of shared data is missing.

**File:** `run_context.php`

```php
<?php

#[\AKlump\FixtureFramework\Fixture(id: 'fixture_a')]
class FixtureA extends \AKlump\FixtureFramework\AbstractFixture {

  public function __invoke(): void {
    $this->runContext->set('fixture_a.required_data', 'lorem');
    $this->runContext->set('fixture_a.optional_data', 'ipsum');
  }
}

#[\AKlump\FixtureFramework\Fixture(id: 'fixture_b')]
class FixtureB extends \AKlump\FixtureFramework\AbstractFixture {

  public function __invoke(): void {
    // This will throw an exception if the required data is not set.
    $lorem = $this->runContext->require('fixture_a.required_data');

    if ($this->runContext->has('fixture_a.optional_data')) {
      $ipsum = $this->runContext->get('fixture_a.optional_data');
      // Do something with $ipsum, which may be an empty string. the `has()`
      // method only checks for existence, not value.
    }
  }
}
```

#### Global Run Options

Run options are provided to the `FixtureRunner` as an array or a `RunOptions` object. Inside a fixture, you can access them via `$this->options` which will always be an instance of `\AKlump\FixtureFramework\RunOptions`.

**Important:** Run options must only contain plain data (null, scalars, or arrays of the same). Objects, closures, and resources are not allowed unless you extend `\AKlump\FixtureFramework\Runtime\RunOptionsValidator` as show below.

```php
  public function __invoke(): void {
    $env = $this->options->get('env');
    $url = $this->options->require('base_url');
    $all = $this->options->all();
```

##### Allowing Custom Run Options

**File:** `RunOptionsValidator.php`

```php
<?php

namespace AKlump\Directio\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Exception\InvalidRunOptionsException;
use Symfony\Component\Console\Output\OutputInterface;

class RunOptionsValidator extends \AKlump\FixtureFramework\Runtime\RunOptionsValidator {

  public function validate(array $options, string $path = ''): void {
    if (isset($options['output'])) {
      if ($options['output'] instanceof OutputInterface) {
        return;
      }
      throw new InvalidRunOptionsException("Invalid output option, it must be an instance of " . OutputInterface::class);
    }

    parent::validate($options, $path);
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

### Runner `setup-fixtures.php`

To run your fixtures, create a script such as `bin/setup-fixtures.php` that bootstraps your application and executes the discovered fixtures for the desired workflow.

**File:** `setup-fixtures.php`

```php
#!/usr/bin/env php
<?php

use AKlump\FixtureFramework\Runtime\FixtureCollectionBuilder;
use AKlump\FixtureFramework\Runtime\FixtureInstantiator;
use AKlump\FixtureFramework\Runtime\FixtureRunner;
use AKlump\FixtureFramework\Runtime\RunContextValidator;
use AKlump\FixtureFramework\Runtime\RunOptions;
use AKlump\FixtureFramework\Runtime\RunOptionsValidator;

$vendor_dir = __DIR__ . '/../vendor';
require_once $vendor_dir . '/autoload.php';

$flush = in_array('--flush', $argv);
$silent = in_array('--silent', $argv);
$filter = '';
foreach ($argv as $arg) {
  if (str_starts_with($arg, '--filter=')) {
    $filter = substr($arg, 9);
    break;
  }
}

try {
  $definitions = (new \AKlump\FixtureFramework\Discovery\DiscoverFixtureDefinitions())($vendor_dir, [
      'MyApp\Tests\Fixture',
  ], $flush, $silent, $filter);
}
catch (Exception $e) {
  echo "Error ordering fixtures: " . $e->getMessage() . "\n";
  exit(1);
}

try {
  /**
   * @var \AKlump\FixtureFramework\Runtime\RunOptionsValidator
   * $run_options_validator This class can be overridden to provide custom
   * runtime options validation.
   */
  $run_options_validator = new RunOptionsValidator();

  /**
   * @var \AKlump\FixtureFramework\Runtime\RunOptions The immutable runtime
   * options provided to all fixtures.
   */
  $run_options = new RunOptions([
      'env' => 'test',
      'url' => 'https://website.com/',
      'drush' => 'lando nxdb_drush',
  ], $run_options_validator);

  /**
   * @var \AKlump\FixtureFramework\Runtime\FixtureInstantiator This class can be
   * overridden to provide custom runtime context validation.
   */
  $run_context_validator = new RunContextValidator();

  /**
   * @var \AKlump\FixtureFramework\Runtime\FixtureInstantiator This class can be
   * overridden to provide custom fixture instantiation if needed.
   */
  $instantiator = new FixtureInstantiator($run_options, $run_context_validator);

  /**
   * @var \AKlump\FixtureFramework\FixtureInterface[] $fixtures The list of
   * fixtures to run as objects.
   */
  $fixtures = (new FixtureCollectionBuilder($instantiator))($definitions);

  /**
   * @var \AKlump\FixtureFramework\Runtime\FixtureRunner The class in charge of
   * running the fixtures.
   */
  $runner = new FixtureRunner($fixtures);
  $runner->run($silent);
}
catch (\Exception $e) {
  print $e->getMessage() . "\n";
  exit(1);
}
```

## Cache Management

`FixtureCache` stores discovered metadata. It invalidates automatically if Composer's autoload files change. Use the `$rebuild_cache` parameter on `\AKlump\FixtureFramework\Helper\GetFixtures` to force discovery.
