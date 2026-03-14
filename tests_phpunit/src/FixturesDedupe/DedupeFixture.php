<?php

namespace AKlump\TestFixture\Tests\FixturesDedupe;

use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Fixture;

#[Fixture(id: 'dedupe', tags: ['tag1', 'tag2', 'tag1', ''])]
class DedupeFixture extends AbstractFixture {
  public function setUp(): void {}
}
