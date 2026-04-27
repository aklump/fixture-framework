<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Helper\GetFixtureIdByClass;
use AKlump\FixtureFramework\Interface\FixtureDefinitionAwareInterface;
use AKlump\FixtureFramework\Interface\RunContextAwareInterface;
use AKlump\FixtureFramework\Interface\RunOptionsAwareInterface;

class FixtureInstantiator {

  private RunOptions $runOptions;

  private ?RunContextValidator $runContextValidator;

  public function __construct(RunOptions $run_options, ?RunContextValidator $run_context_validator = NULL) {
    $this->runOptions = $run_options;
    $this->runContextValidator = $run_context_validator;
  }

  /**
   * @throws \ReflectionException
   */
  public function __invoke(array $definition, RunContextStoreInterface $store): FixtureInterface {
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
    if ($fixture instanceof FixtureDefinitionAwareInterface) {
      $fixture->setFixtureDefinition($definition);
    }
    if ($fixture instanceof RunContextAwareInterface) {
      $fixture->setRunContext(new RunContext($definition['id'], $store, $this->runContextValidator));
    }
    if ($fixture instanceof RunOptionsAwareInterface) {
      $fixture->setRunOptions($this->runOptions);
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
