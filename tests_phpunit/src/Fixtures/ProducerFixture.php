<?php

namespace AKlump\TestFixture\Tests\Fixtures;

use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Fixture;

#[Fixture(id: 'producer', weight: 1, discoverable: true)]
class ProducerFixture extends AbstractFixture {
  public function setUp(array $options): void {
    $this->runContext->set('producer.some_id', 999);
  }
}
