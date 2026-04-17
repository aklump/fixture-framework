# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- Nothing to list

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
