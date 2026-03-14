<?php

namespace AKlump\TestFixture\Tests\FixturesDuplicate;

use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Fixture;

#[Fixture(id: 'fixture_a', weight: 20, discoverable: true)]
class DuplicateFixture extends AbstractFixture {
  public function setUp(): void {
  }
}
