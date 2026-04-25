<?php

#[\AKlump\FixtureFramework\Fixture(id: 'fixture_a')]
class FixtureA extends \AKlump\FixtureFramework\AbstractFixture {

  public function __invoke(): void {
    $this->runContext->set('fixture_a.required_data', 'lorem');
    $this->runContext->set('fixture_a.optional_data', 'ipsum');
  }
}

#[\AKlump\FixtureFramework\Fixture(id: 'fixture_b')]
class FixtureB extends \AKlump\FixtureFramework\AbstractFixture {

  public function __invoke(): void {
    // This will throw an exception if the required data is not set.
    $lorem = $this->runContext->require('fixture_a.required_data');

    if ($this->runContext->has('fixture_a.optional_data')) {
      $ipsum = $this->runContext->get('fixture_a.optional_data');
      // Do something with $ipsum, which may be an empty string. the `has()`
      // method only checks for existence, not value.
    }
  }
}
