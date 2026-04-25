<?php

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;

#[Fixture(id: 'options_example_fixture', weight: -10, after: ['base_schema'])]
class OptionsExampleFixture extends AbstractFixture {

  // <snippet id="using_options">
  public function __invoke(): void {
    $env = $this->options->get('env');
    $url = $this->options->require('base_url');
    $all = $this->options->all();
    // </snippet>
  }
}
