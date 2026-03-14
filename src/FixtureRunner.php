<?php

namespace AKlump\TestFixture;

use AKlump\TestFixture\Exception\FixtureException;

class FixtureRunner {

  private RunOptions $globalOptions;

  /**
   * @param array $fixtures
   * @param array|RunOptions $globalOptions
   */
  public function __construct(
    private array $fixtures,
    array|RunOptions $global_options,
  ) {
    $this->globalOptions = $global_options instanceof RunOptions
      ? $global_options
      : RunOptions::fromArray($global_options);
  }

  public function run(bool $silent = FALSE): void {

    if (!$silent && empty($this->fixtures)) {
      echo "No fixtures found for execution. Check your classes for the #[AKlump\TestFixture\Fixture] attribute." . PHP_EOL;

      return;
    }

    $store = new RunContextStore();
    $validator = new RunContextValidator();

    foreach ($this->fixtures as $fixture_record) {
      $class = $fixture_record['class'];
      $id = $fixture_record['id'];

      if (!$silent) {
        echo sprintf('Executing fixture "%s" (%s)... ', $id, $class) . PHP_EOL;
      }

      try {
        /** @var FixtureInterface $fixture */
        $fixture = new $class();
        if (property_exists($fixture, 'fixture')) {
          $fixture->fixture = $fixture_record;
        }
        if (property_exists($fixture, 'runContext')) {
          $fixture->runContext = new RunContext($id, $store, $validator);
        }
        if (property_exists($fixture, 'options')) {
          $fixture->options = $this->globalOptions;
        }
        $fixture->setUp();
        $fixture->onSuccess($silent);
      }
      catch (FixtureException $e) {
        $fixture->onFailure($e, $silent);
      }
    }
  }

}
