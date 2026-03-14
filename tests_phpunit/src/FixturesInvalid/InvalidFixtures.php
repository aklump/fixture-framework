<?php

namespace AKlump\FixtureFramework\Tests\FixturesInvalid;

use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\FixtureInterface;

abstract class NonInstantiableFixture implements FixtureInterface {
  public function setUp(): void {}
}

class NonImplementingFixture {
}

#[Fixture(id: 'no_interface')]
class NoInterfaceFixture {
}

class NoAttributeFixture implements FixtureInterface {
  public function setUp(): void {}
  public function onSuccess(bool $silent = false) {}
  public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = false) {}
}
