<?php

namespace AKlump\FixtureFramework\Discovery;

class FixtureCache {

  private string $cacheFile;
  private string $vendorDir;

  public function __construct(string $cacheFile, string $vendorDir) {
    $this->cacheFile = $cacheFile;
    $this->vendorDir = $vendorDir;
  }

  public function get(): ?array {
    if (!file_exists($this->cacheFile)) {
      return null;
    }

    if ($this->isInvalidated()) {
      return null;
    }

    return json_decode(file_get_contents($this->cacheFile), true);
  }

  public function set(array $fixtures): void {
    file_put_contents($this->cacheFile, json_encode($fixtures));
    $this->updateLastModified();
  }

  public function rebuild(FixtureDiscovery $discovery, array $namespace_allow_list = []): array {
    $fixtures = $discovery->discover($namespace_allow_list);
    $this->set($fixtures);
    return $fixtures;
  }

  private function isInvalidated(): bool {
    $mtime = filemtime($this->cacheFile);

    foreach (['autoload_psr4.php', 'autoload_classmap.php'] as $file) {
      $path = $this->vendorDir . '/composer/' . $file;
      if (file_exists($path) && filemtime($path) > $mtime) {
        return true;
      }
    }

    return false;
  }

  private function updateLastModified(): void {
    // We already use filemtime, so setting content is enough.
  }
}
