<?php

namespace AKlump\TestFixture;

use AKlump\TestFixture\Exception\FixtureException;
use AKlump\TestFixture\Exception\InvalidFixtureOptionsException;

class FixtureRunner {

  public function __construct(
    private array $fixtures,
    private array $globalOptions,
  ) {
  }

  public function run(bool $silent = FALSE): void {
    $this->assertOptionsArePlainData($this->globalOptions);

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

  /**
   * Recurse the options array to ensure it only contains null, scalar, or array values.
   *
   * @param array $options
   *   The options array to validate.
   * @param string $path
   *   The current traversal path for identifying the location of invalid values.
   *
   * @throws \AKlump\TestFixture\Exception\InvalidFixtureOptionsException
   */
  private function assertOptionsArePlainData(array $options, string $path = ''): void {
    foreach ($options as $key => $value) {
      $current_path = $path === '' ? (string) $key : "$path.$key";
      if (is_array($value)) {
        $this->assertOptionsArePlainData($value, $current_path);
      }
      elseif (NULL !== $value && !is_scalar($value)) {
        throw InvalidFixtureOptionsException::forPath($current_path);
      }
    }
  }

}
