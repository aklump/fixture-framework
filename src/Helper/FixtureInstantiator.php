<?php

namespace AKlump\FixtureFramework\Helper;

use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Runtime\RunContext;
use AKlump\FixtureFramework\Runtime\RunContextStore;
use AKlump\FixtureFramework\Runtime\RunContextValidator;
use AKlump\FixtureFramework\Runtime\RunOptions;

class FixtureInstantiator {

  /**
   * @param array $definition
   * @param array|RunOptions $global_options
   * @param \AKlump\FixtureFramework\RunContextStore $store
   * @param \AKlump\FixtureFramework\RunContextValidator $validator
   *
   * @return \AKlump\FixtureFramework\FixtureInterface
   */
  public function __invoke(
    array $definition,
    RunOptions|array $global_options,
    RunContextStore $store,
    RunContextValidator $validator,
  ): FixtureInterface {
    if (empty($definition['class'])) {
      throw new \InvalidArgumentException('Fixture record must have a class.');
    }
    $class = $definition['class'];

    if (empty($definition['id'])) {
      $definition['id'] = (new GetFixtureIdByClass())($class);
    }
    if (empty($definition['id'])) {
      throw new \InvalidArgumentException(sprintf('Fixture id must be a non-empty string on class "%s".', $class));
    }
    $id = $definition['id'];

    $fixture = new $class();
    if (property_exists($fixture, 'fixture')) {
      $fixture->fixture = $definition;
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
