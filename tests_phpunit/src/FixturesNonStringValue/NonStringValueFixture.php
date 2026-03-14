<?php

namespace AKlump\TestFixture\Tests\FixturesNonStringValue;

use AKlump\TestFixture\Fixture;
use AKlump\TestFixture\AbstractFixture;

#[Fixture(id: 'non_string', after: [123])]
class NonStringValueFixture extends AbstractFixture {
  public function setUp(): void {}
}
