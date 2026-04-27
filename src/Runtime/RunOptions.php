<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Exception\MissingRunOptionException;

/**
 * Read-only run options API.
 */
class RunOptions {

  private array $options;

  protected RunOptionsValidator $validator;

  public function __construct(array $options = [], RunOptionsValidator $validator = NULL) {
    $this->validator = $validator ?? new RunOptionsValidator();
    $this->validator->validate($options);
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

  /**
   * Adds additional options to the current options and returns a new instance.
   *
   * @param array $options An associative array of options to add.
   * These options must not contain keys that already exist in the current
   * options, as overriding those keys will result in an exception.
   *
   * @return RunOptions A new instance containing the merged options.
   * @throws \InvalidArgumentException If any keys in the extended options
   * conflict with the existing keys.
   */
  public function withAddedOptions(array $options): self {
    $current_options = $this->options;
    $conflicts = array_intersect_key($current_options, $options);
    if ($conflicts) {
      throw new \InvalidArgumentException('The following keys may not be overridden: ' . implode(', ', array_keys($conflicts)));
    }
    $current_options += $options;

    return new self($current_options, $this->validator);
  }

}
