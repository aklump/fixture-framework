<?php

namespace AKlump\FixtureFramework\Tests\Fixtures;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'consumer', after: ['producer'], discoverable: true)]
class ConsumerFixture extends AbstractFixture {
  public static mixed $consumedValue = null;
  public function setUp(): void {
    self::$consumedValue = $this->runContext->require('producer.some_id');
  }
}
