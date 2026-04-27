<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException;

class RunContextStoreFile implements RunContextStoreInterface {

  private string $filePath;

  /**
   * @var array
   */
  private array $cache = [];

  /**
   * @var bool
   */
  private bool $isLoaded = false;

  /**
   * @param string $file_path The path to the file where the context is stored.
   */
  public function __construct(string $file_path) {
    $this->filePath = $file_path;
  }

  /**
   * Load the data from the file into memory.
   */
  private function load(): void {
    if ($this->isLoaded) {
      return;
    }
    if (file_exists($this->filePath)) {
      $file_contents = file_get_contents($this->filePath);
      if ($file_contents) {
        $this->cache = unserialize($file_contents) ?: [];
      }
    }
    $this->isLoaded = true;
  }

  /**
   * Save the data from memory to the file.
   */
  private function save(): void {
    $directory = dirname($this->filePath);
    if (!is_dir($directory)) {
      mkdir($directory, 0777, true);
    }
    file_put_contents($this->filePath, serialize($this->cache));
  }

  /**
   * {@inheritdoc}
   */
  public function set(string $key, mixed $value): void {
    $this->load();
    if (array_key_exists($key, $this->cache)) {
      throw new RunContextKeyAlreadyExistsException($key);
    }
    $this->cache[$key] = $value;
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $key, mixed $default = null): mixed {
    $this->load();

    return array_key_exists($key, $this->cache) ? $this->cache[$key] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function has(string $key): bool {
    $this->load();

    return array_key_exists($key, $this->cache);
  }

  /**
   * {@inheritdoc}
   */
  public function all(): array {
    $this->load();

    return $this->cache;
  }

}
