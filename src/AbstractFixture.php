<?php

namespace AKlump\FixtureFramework;

use AKlump\FixtureFramework\Exception\FixtureException;

abstract class AbstractFixture implements FixtureInterface {

  use FixtureMetadataTrait;
  use FixtureRunContextTrait;
  use FixtureOptionsTrait;

  public function onSuccess(bool $silent = FALSE) {
    if (!$silent) {
      echo "Done." . PHP_EOL;
    }
  }

  public function onFailure(FixtureException $e, bool $silent = FALSE) {
    throw $e;
  }
}
