# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- Fixture auto-wiring is now interface-driven.
- Added `FixtureDefinitionAwareInterface`, `RunOptionsAwareInterface`, and `RunContextAwareInterface`.
- Existing traits now provide setter/accessor methods.
- Fixture instantiation dependencies (`RunOptions`, `RunContextValidator`) have moved into `FixtureInstantiator` constructor.
- `FixtureInstantiator::__invoke()` now focuses on instantiating a single fixture definition.
- `FixtureCollectionBuilder` now receives a pre-configured `FixtureInstantiator`.
- `FixtureCollectionBuilder` now owns the creation of the shared `RunContextStore`.
- `RunOptions` constructor now has optional `$options` and `$validator` parameters.

### Migration Guide

#### Fixture auto-wiring

Fixture auto-wiring is now based on explicit capability interfaces instead of public property detection.

If your fixture extends `AbstractFixture`, no immediate change is required. The existing public properties still work, and new accessor methods are available:

- `$this->fixture()` instead of `$this->fixture`
- `$this->options()` instead of `$this->options`
- `$this->context()` instead of `$this->runContext`

If your fixture does not extend `AbstractFixture` but previously declared public properties such as `$options`, `$fixture`, or `$runContext` to receive injection, update it to implement the appropriate interface:

- `FixtureDefinitionAwareInterface`
- `RunOptionsAwareInterface`
- `RunContextAwareInterface`

#### `FixtureInstantiator`

If you were manually invoking the instantiator, update your code as follows:

```php
// Before
$instantiator = new FixtureInstantiator();
$fixture = $instantiator($definition, $options, $store, $validator);

// After
$instantiator = new FixtureInstantiator($options, $validator);
$fixture = $instantiator($definition, $store);
```

#### `FixtureCollectionBuilder`

Update `FixtureCollectionBuilder` construction to pass a pre-configured `FixtureInstantiator`:

```php
// Before
$builder = new FixtureCollectionBuilder($options, $validator);
$fixtures = $builder($definitions);

// After
$instantiator = new FixtureInstantiator($options, $validator);
$builder = new FixtureCollectionBuilder($instantiator);
$fixtures = $builder($definitions);
```

## [0.0.9] - 2026-04-17

### Changed

- bin/setup-fixtures to bin/setup-fixtures.php

## [0.0.8] - 2026-04-12

### Added

- Added `\AKlump\FixtureFramework\FixtureInterface::id`
- Added structured discovery of fixture definitions before runtime instantiation.

### Changed

- Fixture setup now flows through separate discovery and runtime phases
- Fixture identity is resolved earlier in the lifecycle @see `\AKlump\FixtureFramework\Runtime\FixtureCollectionBuilder`
- Shared run context is now injected during fixture instantiation.
- Documentation and setup examples were updated to match the new execution model.
- Internal classes were reorganized into `Discovery`, `Runtime`, and `Traits` namespaces.
- 🚨BREAKING CHANGE! Replace every fixture `setUp()` method with `__invoke()`.
- 🚨BREAKING CHANGE! If your fixture relied on lazy ID lookup, resolve the ID during instantiation instead.
- 🚨BREAKING CHANGE! Update any code that imports or instantiates renamed classes from the old structure.
- 🚨BREAKING CHANGE! `setup-runner.php` has changed significantly, update your setup scripts accordingly.
