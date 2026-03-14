<?php

namespace AKlump\TestFixture\Tests\FixturesInvalid;

use AKlump\TestFixture\Fixture;
use AKlump\TestFixture\FixtureInterface;

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
  public function onFailure(\AKlump\TestFixture\Exception\FixtureException $e, bool $silent = false) {}
}
