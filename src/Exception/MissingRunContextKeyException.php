<?php

namespace AKlump\FixtureFramework\Exception;

class MissingRunContextKeyException extends FixtureException {

  public function __construct(string $fixtureId, string $key) {
    parent::__construct(sprintf(
      'Fixture "%s" requires run context key "%s", but it has not been set.',
      $fixtureId,
      $key
    ));
  }

}
