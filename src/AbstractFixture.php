<?php

namespace AKlump\FixtureFramework;

use AKlump\FixtureFramework\Exception\FixtureException;
use AKlump\FixtureFramework\Interface\FixtureDefinitionAwareInterface;
use AKlump\FixtureFramework\Interface\RunOptionsAwareInterface;
use AKlump\FixtureFramework\Interface\RunContextAwareInterface;
use AKlump\FixtureFramework\Traits\FixtureMetadataTrait;
use AKlump\FixtureFramework\Traits\FixtureRunContextTrait;
use AKlump\FixtureFramework\Traits\FixtureOptionsTrait;

abstract class AbstractFixture implements
  FixtureInterface,
  FixtureDefinitionAwareInterface,
  RunOptionsAwareInterface,
  RunContextAwareInterface {

  use FixtureMetadataTrait;
  use FixtureRunContextTrait;
  use FixtureOptionsTrait;

  public function id(): string {
    return $this->fixture()['id'] ?? '';
  }

  public function onSuccess(bool $silent = FALSE) {
    if (!$silent) {
      echo "Done." . PHP_EOL;
    }
  }

  public function onFailure(FixtureException $e, bool $silent = FALSE) {
    throw $e;
  }

}
