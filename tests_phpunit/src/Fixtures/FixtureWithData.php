<?php

namespace AKlump\FixtureFramework\Tests\Fixtures;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'fixture_with_data', weight: 42, tags: ['tag1', 'tag2'])]
class FixtureWithData extends AbstractFixture {
  public array $fixture;
  public static array $received = [];
  public function __invoke(): void {
    if (isset($this->fixture)) {
      self::$received = $this->fixture;
    }
  }
}
