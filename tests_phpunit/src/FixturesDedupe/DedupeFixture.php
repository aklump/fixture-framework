<?php

namespace AKlump\FixtureFramework\Tests\FixturesDedupe;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'dedupe', tags: ['tag1', 'tag2', 'tag1', ''])]
class DedupeFixture extends AbstractFixture {
  public function __invoke(): void {}
}
