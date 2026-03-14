<?php

namespace AKlump\TestFixture\Tests\Fixtures;

use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Fixture;

#[Fixture(id: 'consumer', after: ['producer'], discoverable: true)]
class ConsumerFixture extends AbstractFixture {
  public static mixed $consumedValue = null;
  public function setUp(): void {
    self::$consumedValue = $this->runContext->require('producer.some_id');
  }
}
