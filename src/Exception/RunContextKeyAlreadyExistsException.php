<?php

namespace AKlump\FixtureFramework\Exception;

class RunContextKeyAlreadyExistsException extends FixtureException {

  public function __construct(string $key) {
    parent::__construct(sprintf(
      'Run context key "%s" has already been set and cannot be overwritten.',
      $key
    ));
  }

}
