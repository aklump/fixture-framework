<?php

namespace AKlump\FixtureFramework;

use AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException;

class RunContextStore {

  private array $data = [];

  public function set(string $key, mixed $value): void {
    if (array_key_exists($key, $this->data)) {
      throw new RunContextKeyAlreadyExistsException($key);
    }
    $this->data[$key] = $value;
  }

  public function get(string $key, mixed $default = null): mixed {
    return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
  }

  public function has(string $key): bool {
    return array_key_exists($key, $this->data);
  }

  public function all(): array {
    return $this->data;
  }

}
