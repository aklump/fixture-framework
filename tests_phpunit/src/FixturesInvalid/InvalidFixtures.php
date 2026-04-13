<?php

namespace AKlump\FixtureFramework\Tests\FixturesInvalid;

use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\FixtureInterface;

abstract class NonInstantiableFixture implements FixtureInterface {
  public function id(): string {
    return '';
  }
  public function __invoke(): void {}
  public function onSuccess(bool $silent = FALSE) {}
  public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = FALSE) {}
}

class NonImplementingFixture {
}

#[Fixture(id: 'no_interface')]
class NoInterfaceFixture {
}

class NoAttributeFixture implements FixtureInterface {
  public function id(): string {
    return '';
  }
  public function __invoke(): void {}
  public function onSuccess(bool $silent = false) {}
  public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = false) {}
}
