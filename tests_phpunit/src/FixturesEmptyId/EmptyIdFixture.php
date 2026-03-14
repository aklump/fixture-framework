<?php

namespace AKlump\TestFixture\Tests\FixturesEmptyId;

use AKlump\TestFixture\Fixture;
use AKlump\TestFixture\AbstractFixture;

#[Fixture(id: '')]
class EmptyIdFixture extends AbstractFixture {
  public function setUp(): void {}
}
