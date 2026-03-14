<?php

namespace AKlump\TestFixture\Exception;

/**
 * Exception thrown when a required run option is missing.
 */
class MissingRunOptionException extends \RuntimeException {

  public function __construct(string $key) {
    parent::__construct(sprintf('Required run option "%s" is missing.', $key));
  }

}
