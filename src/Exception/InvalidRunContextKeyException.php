<?php

namespace AKlump\FixtureFramework\Exception;

class InvalidRunContextKeyException extends FixtureException {

  public function __construct(string $fixtureId, string $key) {
    parent::__construct(sprintf(
      'Run context key "%s" must begin with "%s.".',
      $key,
      $fixtureId
    ));
  }

}
