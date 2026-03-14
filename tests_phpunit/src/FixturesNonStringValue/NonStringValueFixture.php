<?php

namespace AKlump\FixtureFramework\Tests\FixturesNonStringValue;

use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\AbstractFixture;

#[Fixture(id: 'non_string', after: [123])]
class NonStringValueFixture extends AbstractFixture {
  public function setUp(): void {}
}
