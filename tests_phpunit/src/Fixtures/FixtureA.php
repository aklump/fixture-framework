<?php

namespace AKlump\FixtureFramework\Tests\Fixtures;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'fixture_a', weight: 10, discoverable: true)]
class FixtureA extends AbstractFixture {
  public static bool $called = false;
  public function __invoke(): void {
    self::$called = true;
  }
}
