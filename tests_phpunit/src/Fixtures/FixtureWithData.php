<?php

namespace AKlump\TestFixture\Tests\Fixtures;

use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Fixture;

#[Fixture(id: 'fixture_with_data', weight: 42, tags: ['tag1', 'tag2'])]
class FixtureWithData extends AbstractFixture {
  public array $fixture;
  public static array $received = [];
  public function setUp(): void {
    if (isset($this->fixture)) {
      self::$received = $this->fixture;
    }
  }
}
