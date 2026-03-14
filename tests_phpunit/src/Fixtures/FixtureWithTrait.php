<?php

namespace AKlump\TestFixture\Tests\Fixtures;

use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Fixture;
use AKlump\TestFixture\FixtureMetadataTrait;

#[Fixture(id: 'fixture_with_trait', weight: 42, discoverable: false)]
class FixtureWithTrait extends AbstractFixture {

  use FixtureMetadataTrait;

  public static array $received = [];

  public function setUp(): void {
    self::$received = $this->fixture;
  }
}
