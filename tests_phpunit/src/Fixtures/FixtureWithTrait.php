<?php

namespace AKlump\FixtureFramework\Tests\Fixtures;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\FixtureMetadataTrait;

#[Fixture(id: 'fixture_with_trait', weight: 42, discoverable: false)]
class FixtureWithTrait extends AbstractFixture {

  use FixtureMetadataTrait;

  public static array $received = [];

  public function setUp(): void {
    self::$received = $this->fixture;
  }
}
