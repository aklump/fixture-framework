<?php

namespace AKlump\TestFixture;

use AKlump\TestFixture\Exception\FixtureException;

class FixtureRunner {

  public function __construct(
    private array $fixtures,
    private array $globalOptions,
  ) {
  }

  public function run(bool $silent = FALSE): void {
    if (!$silent && empty($this->fixtures)) {
      echo "No fixtures found for execution. Check your classes for the #[AKlump\TestFixture\Fixture] attribute." . PHP_EOL;

      return;
    }
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
        $fixture->setUp($this->globalOptions);
        $fixture->onSuccess($silent);
      }
      catch (FixtureException $e) {
        $fixture->onFailure($e, $silent);
      }
    }
  }
}
