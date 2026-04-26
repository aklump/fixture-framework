<?php

namespace AKlump\FixtureFramework\Tests\Fixtures;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'fixture_with_description', description: 'This is a test description')]
class FixtureWithDescription extends AbstractFixture {
  public function __invoke(): void {
  }
}
