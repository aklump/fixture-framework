<?php

namespace AKlump\FixtureFramework\Runtime;

interface RunContextStoreInterface {

  /**
   * Set a value in the store.
   *
   * @param string $key
   * @param mixed $value
   *
   * @throws \AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException
   */
  public function set(string $key, mixed $value): void;

  /**
   * Get a value from the store.
   *
   * @param string $key
   * @param mixed $default
   *
   * @return mixed
   */
  public function get(string $key, mixed $default = null): mixed;

  /**
   * Check if a key exists in the store.
   *
   * @param string $key
   *
   * @return bool
   */
  public function has(string $key): bool;

  /**
   * Get all values from the store.
   *
   * @return array
   */
  public function all(): array;

  public function remove(string $key): void;

}
