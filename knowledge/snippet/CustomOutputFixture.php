<?php

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\Exception\FixtureException;

#[Fixture(id: 'custom_output')]
class CustomOutputFixture extends AbstractFixture {

  public function __invoke(): void {
    // ...
  }

  public function onSuccess(bool $silent = FALSE) {
    if (!$silent) {
      echo "✅ Successfully completed!" . PHP_EOL;
    }
  }

  public function onFailure(FixtureException $e, bool $silent = FALSE) {
    if (!$silent) {
      echo "❌ Failed: " . $e->getMessage() . PHP_EOL;
    }
    throw $e;
  }

}
