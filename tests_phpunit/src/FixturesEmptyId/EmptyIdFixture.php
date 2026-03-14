<?php

namespace AKlump\FixtureFramework\Tests\FixturesEmptyId;

use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\AbstractFixture;

#[Fixture(id: '')]
class EmptyIdFixture extends AbstractFixture {
  public function setUp(): void {}
}
