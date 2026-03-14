<?php

namespace AKlump\TestFixture\Exception;

/**
 * Exception thrown when fixture runner options contain unsupported types.
 *
 * This exception is raised by FixtureRunner when provided global options
 * contain values that are not null, scalar (bool, int, float, string),
 * or arrays recursively.
 */
class InvalidFixtureOptionsException extends \Exception {

  /**
   * Create a new instance for a specific invalid path.
   *
   * @param string $path
   *   The path to the invalid value (e.g. "api.client", "servers.0.host").
   *
   * @return self
   */
  public static function forPath(string $path): self {
    $message = sprintf('Fixture runner options must contain only null, scalar, or array values. Invalid value found at "%s".', $path);

    return new self($message);
  }

}
