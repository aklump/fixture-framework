<?php

namespace AKlump\TestFixture\Helper;

use AKlump\TestFixture\FixtureCache;
use AKlump\TestFixture\FixtureDiscovery;
use AKlump\TestFixture\FixtureOrderer;

class GetFixtures {

  public function __invoke(string $vendor_dir = '', array $namespace_allow_list = [], bool $rebuild_cache = FALSE, bool $silent = FALSE, string $filter = ''): array {
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
    $fixtures = $orderer->order($fixtures);

    if ($filter !== '') {
      $filter = trim($filter);
      $pattern = $this->toPregPattern($filter);

      $fixtures = array_filter(
        $fixtures,
        static fn(array $fixture): bool => preg_match($pattern, $fixture['id']) === 1
      );
    }

    return $fixtures;
  }

  private function toPregPattern(string $filter): string {
    if ($filter === '') {
      return '//';
    }

    if ($this->isDelimitedRegex($filter)) {
      return $filter;
    }

    return '/' . str_replace('/', '\/', $filter) . '/';
  }

  private function isDelimitedRegex(string $pattern): bool {
    $start = $pattern[0] ?? '';
    if ($start === '' || ctype_alnum($start) || ctype_space($start) || $start === '\\') {
      return FALSE;
    }
    $pairs = [
      '(' => ')',
      '[' => ']',
      '{' => '}',
      '<' => '>',
    ];
    $endDelimiter = $pairs[$start] ?? $start;
    $length = strlen($pattern);
    for ($i = $length - 1; $i > 0; --$i) {
      if ($pattern[$i] !== $endDelimiter) {
        continue;
      }
      if ($pattern[$i - 1] === '\\') {
        continue;
      }
      $modifiers = substr($pattern, $i + 1);

      return $modifiers === '' || preg_match('/^[imsxuADUJ]*$/', $modifiers) === 1;
    }

    return FALSE;
  }

  private function normalizeNamespaces(array $namespace_allow_list) {
    // @see \AKlump\TestFixture\FixtureDiscovery::discover
    return array_map(fn($namespace) => trim($namespace, '\\') . '\\', $namespace_allow_list);
  }
}
