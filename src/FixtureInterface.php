<?php

namespace AKlump\TestFixture;

use AKlump\TestFixture\Exception\FixtureException;

interface FixtureInterface {

  public function setUp(): void;

  public function onSuccess(bool $silent = FALSE);

  public function onFailure(FixtureException $e, bool $silent = FALSE);
}
