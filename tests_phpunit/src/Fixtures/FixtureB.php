<?php

namespace AKlump\FixtureFramework\Tests\Fixtures;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'fixture_b', weight: 5, after: ['fixture_a'], discoverable: true)]
class FixtureB extends AbstractFixture {
  public static bool $called = false;
  public function setUp(): void {
    self::$called = true;
  }
}
