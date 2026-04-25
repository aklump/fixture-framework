<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Helper\GetFixtureIdByClass;

class FixtureInstantiator {

  private RunOptions $globalOptions;

  private RunContextValidator $validator;

  public function __construct(array|RunOptions $global_options, RunContextValidator $validator) {
    $this->globalOptions = $global_options instanceof RunOptions
      ? $global_options
      : RunOptions::fromArray($global_options);
    $this->validator = $validator;
  }

  /**
   * @throws \ReflectionException
   */
  public function __invoke(array $definition, RunContextStore $store): FixtureInterface {
    /**
     * Validate the fixture definition.
     */
    if (empty($definition['class'])) {
      throw new \InvalidArgumentException('Fixture record must have a class.');
    }
    if (!is_a($definition['class'], FixtureInterface::class, TRUE)) {
      throw new \InvalidArgumentException(sprintf('Fixture class "%s" must implement FixtureInterface.', $definition['class']));
    }
    if (empty($definition['id'])) {
      $definition['id'] = (new GetFixtureIdByClass())($definition['class']);
    }
    if (empty($definition['id'])) {
      throw new \InvalidArgumentException(sprintf('Fixture id must be a non-empty string on class "%s".', $definition['class']));
    }

    /**
     * Instantiate the fixture before auto-wiring runtime properties.
     */
    $fixture = $this->createFixture($definition);

    /**
     * Handle auto-wiring of fixture properties.
     */
    if (property_exists($fixture, 'fixture')) {
      $fixture->fixture = $definition;
    }
    if (property_exists($fixture, 'options')) {
      $fixture->options = $this->globalOptions;
    }
    if (property_exists($fixture, 'runContext')) {
      $fixture->runContext = new RunContext($definition['id'], $store, $this->validator);
    }

    return $fixture;
  }

  /**
   * Creates a fixture instance using the provided definition.
   *
   * Override this method to customize fixture construction. Dependencies
   * required by a custom instantiation strategy should be injected into the
   * FixtureInstantiator subclass itself, not added to this method signature.
   *
   * @param array $definition The normalized fixture definition most importantly
   * the `class` key containing the fully-qualified class name, which has
   * already been validated.
   *
   * @return FixtureInterface An instance of the fixture.
   */
  protected function createFixture(array $definition): FixtureInterface {
    return new $definition['class']();
  }
}
