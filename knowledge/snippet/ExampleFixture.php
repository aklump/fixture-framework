<?php

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

// <snippet id="fixture_attributes">
#[Fixture(id: 'example_fixture', weight: -10, after: ['base_schema'])]
class ExampleFixture extends AbstractFixture {

  // </snippet>
  // <snippet id="get_fixture_id">
  public function __invoke(): void {
    $fixture_id = $this->fixture['id'];
    // </snippet>
  }
}
