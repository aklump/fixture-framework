<?php

namespace AKlump\FixtureFramework;

use AKlump\FixtureFramework\Exception\FixtureException;
use AKlump\FixtureFramework\Helper\FixtureInstantiator;

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
      echo "No fixtures found for execution. Check your classes for the #[AKlump\FixtureFramework\Fixture] attribute." . PHP_EOL;

      return;
    }

    $store = new RunContextStore();
    $validator = new RunContextValidator();

    foreach ($this->fixtures as $fixture_record) {
      $class = $fixture_record['class'];
      $id = $fixture_record['id'];

      if (!$silent) {
        echo sprintf('Running fixture "%s" (%s)... ', $id, $class) . PHP_EOL;
      }

      try {
        $fixture = (new FixtureInstantiator())($fixture_record, $this->globalOptions, $store, $validator);
        $fixture->__invoke();
        $fixture->onSuccess($silent);
      }
      catch (FixtureException $e) {
        $fixture->onFailure($e, $silent);
      }
    }
  }

}
