<?php

namespace AKlump\FixtureFramework\Tests\FixturesDuplicate;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'fixture_a', weight: 20, discoverable: true)]
class DuplicateFixture extends AbstractFixture {
  public function setUp(): void {
  }
}
