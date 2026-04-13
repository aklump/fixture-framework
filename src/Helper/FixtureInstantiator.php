<?php

namespace AKlump\FixtureFramework\Helper;

use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\RunContext;
use AKlump\FixtureFramework\RunContextStore;
use AKlump\FixtureFramework\RunContextValidator;
use AKlump\FixtureFramework\RunOptions;

class FixtureInstantiator {

  /**
   * @param array $fixture_record
   * @param array|RunOptions $global_options
   * @param \AKlump\FixtureFramework\RunContextStore $store
   * @param \AKlump\FixtureFramework\RunContextValidator $validator
   *
   * @return \AKlump\FixtureFramework\FixtureInterface
   */
  public function __invoke(
    array $fixture_record,
    RunOptions|array $global_options,
    RunContextStore $store,
    RunContextValidator $validator,
  ): FixtureInterface {
    if (empty($fixture_record['id'])) {
      throw new \InvalidArgumentException('Fixture record must have an id.');
    }
    if (empty($fixture_record['class'])) {
      throw new \InvalidArgumentException('Fixture record must have a class.');
    }
    $id = $fixture_record['id'];
    $class = $fixture_record['class'];

    $fixture = new $class();
    if (property_exists($fixture, 'fixture')) {
      $fixture->fixture = $fixture_record;
    }

    if (property_exists($fixture, 'options')) {
      $global_options = $global_options instanceof RunOptions
        ? $global_options
        : RunOptions::fromArray($global_options);
      $fixture->options = $global_options;
    }

    if (property_exists($fixture, 'runContext')) {
      $fixture->runContext = new RunContext($id, $store, $validator);
    }

    return $fixture;
  }
}
