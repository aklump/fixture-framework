<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Exception\FixtureException;
use AKlump\FixtureFramework\FixtureInterface;

class FixtureRunner {

  /**
   * @param \AKlump\FixtureFramework\FixtureInterface[] $fixtures
   */
  public function __construct(private array $fixtures) {
    foreach ($this->fixtures as $fixture) {
      if (!$fixture instanceof FixtureInterface) {
        throw new \InvalidArgumentException(sprintf(
          'All fixtures must implement %s; %s does not.',
          FixtureInterface::class,
          is_object($fixture) ? get_class($fixture) : gettype($fixture)
        ));
      }
    }
  }

  /**
   * Run all fixtures.
   *
   * @param bool $silent Defaults to FALSE.  If TRUE, no output will be printed.
   * @param string|NULL $working_directory Defaults to the current working directory.  All fixtures will be run in this directory.
   *
   * @return void
   */
  public function run(bool $silent = FALSE, string $working_directory = NULL): void {
    if (!$silent && empty($this->fixtures)) {
      echo "No fixtures found for execution. Check your classes for the #[AKlump\FixtureFramework\Fixture] attribute." . PHP_EOL;

      return;
    }
    $starting_directory = getcwd();
    if ($working_directory && !@chdir($working_directory)){
      throw new FixtureException("Unable to change working directory to '$working_directory'.");
    }
    try {
      foreach ($this->fixtures as $fixture) {
        if (!$silent) {
          echo sprintf('Running fixture "%s" (%s)... ', $fixture->id(), get_class($fixture)) . PHP_EOL;
        }
        try {
          $fixture->__invoke();
          $fixture->onSuccess($silent);
        }
        catch (FixtureException $e) {
          $fixture->onFailure($e, $silent);
        }
      }
    }
    finally {
      if ($working_directory) {
        chdir($starting_directory);
      }
    }
  }

}
