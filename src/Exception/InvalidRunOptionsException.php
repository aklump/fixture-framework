<?php

namespace AKlump\FixtureFramework\Exception;

/**
 * Exception thrown when run options contain invalid types.
 */
class InvalidRunOptionsException extends \InvalidArgumentException {

  public static function forPath(string $path): self {
    $message = sprintf('Run options must contain only null, scalar, or array values. Invalid value found at "%s".', $path);

    return new self($message);
  }

}
