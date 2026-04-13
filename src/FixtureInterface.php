<?php

namespace AKlump\FixtureFramework;

use AKlump\FixtureFramework\Exception\FixtureException;

interface FixtureInterface {

  public function __invoke(): void;

  public function onSuccess(bool $silent = FALSE);

  public function onFailure(FixtureException $e, bool $silent = FALSE);
}
