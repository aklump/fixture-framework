<?php

namespace AKlump\FixtureFramework;

use AKlump\FixtureFramework\Exception\MissingRunOptionException;

/**
 * Read-only run options API.
 */
class RunOptions {

  private array $options;

  public function __construct(array $options, RunOptionsValidator $validator = NULL) {
    if (NULL === $validator) {
      $validator = new RunOptionsValidator();
    }
    $validator->validate($options);
    $this->options = $options;
  }

  public static function fromArray(array $options): self {
    return new self($options);
  }

  public function get(string $key, mixed $default = NULL): mixed {
    return $this->options[$key] ?? $default;
  }

  public function has(string $key): bool {
    return array_key_exists($key, $this->options);
  }

  public function require(string $key): mixed {
    if (!$this->has($key)) {
      throw new MissingRunOptionException($key);
    }

    return $this->options[$key];
  }

  public function all(): array {
    return $this->options;
  }

}
