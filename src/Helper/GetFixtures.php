<?php

namespace AKlump\TestFixture\Helper;

use AKlump\TestFixture\FixtureCache;
use AKlump\TestFixture\FixtureDiscovery;
use AKlump\TestFixture\FixtureOrderer;

class GetFixtures {

  public function __invoke(string $vendor_dir = '', array $namespace_allow_list = [], bool $rebuild_cache = FALSE, bool $silent = FALSE): array {
    if (!is_dir($vendor_dir)) {
      throw new \InvalidArgumentException("Is not a directory: $vendor_dir");
    }
    elseif (basename($vendor_dir) !== 'vendor') {
      throw new \InvalidArgumentException("Must be a Composer vendor dir: $vendor_dir");
    }

    $cache_file = getenv('TEST_FIXTURE_CACHE_FILE') ?: '';

    if ($cache_file === '') {
      $vendor_dir_real = $vendor_dir !== '' ? (realpath($vendor_dir) ?: $vendor_dir) : '';
      $cache_key_parts = [$vendor_dir_real];
      if (!empty($namespace_allow_list)) {
        $namespace_allow_list = $this->normalizeNamespaces($namespace_allow_list);
        $sorted_allow_list = $namespace_allow_list;
        sort($sorted_allow_list);
        $cache_key_parts[] = implode('|', $sorted_allow_list);
      }
      $cache_key = !empty($cache_key_parts) ? sha1(implode(':', $cache_key_parts)) : 'default';

      $cache_dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'test_fixture';
      if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0777, TRUE);
      }

      $cache_file = $cache_dir . DIRECTORY_SEPARATOR . "fixtures.$cache_key.cache.json";
    }

    $discovery = (new FixtureDiscovery($vendor_dir))->setSilent($silent);
    $cache = new FixtureCache($cache_file, $vendor_dir);

    if ($rebuild_cache) {
      $fixtures = $cache->rebuild($discovery, $namespace_allow_list);
    }
    else {
      $fixtures = $cache->get();
      if ($fixtures === NULL) {
        $fixtures = $cache->rebuild($discovery, $namespace_allow_list);
      }
    }

    $orderer = new FixtureOrderer();

    return $orderer->order($fixtures);
  }

  private function normalizeNamespaces(array $namespace_allow_list) {
    // @see \AKlump\TestFixture\FixtureDiscovery::discover
    return array_map(fn($namespace) => trim($namespace, '\\') . '\\', $namespace_allow_list);
  }
}
