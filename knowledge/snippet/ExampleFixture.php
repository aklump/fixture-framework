<?php

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

// <snippet id="fixture_attributes">
#[Fixture(
  id: 'example_fixture',
  description: 'An example fixture that demonstrates metadata.',
  weight: -10,
  after: ['base_schema']
)]
class ExampleFixture extends AbstractFixture {

  // </snippet>
  // <snippet id="get_fixture_id">
  public function __invoke(): void {
    $id = $this->id();
    $description = $this->description();
    $weight = $this->fixture()['weight'] ?? 0;
    // </snippet>
  }
}
