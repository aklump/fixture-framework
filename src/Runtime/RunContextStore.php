<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException;

class RunContextStore implements RunContextStoreInterface {

  private array $data = [];

  /**
   * {@inheritdoc}
   */
  public function set(string $key, mixed $value): void {
    if (array_key_exists($key, $this->data)) {
      throw new RunContextKeyAlreadyExistsException($key);
    }
    $this->data[$key] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $key, mixed $default = null): mixed {
    return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function has(string $key): bool {
    return array_key_exists($key, $this->data);
  }

  /**
   * {@inheritdoc}
   */
  public function all(): array {
    return $this->data;
  }

}
