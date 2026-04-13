<?php

namespace AKlump\FixtureFramework\Tests\Fixtures;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'producer', weight: 1, discoverable: true)]
class ProducerFixture extends AbstractFixture {
  public function __invoke(): void {
    $this->runContext->set('producer.some_id', 999);
  }
}
