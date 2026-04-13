<?php

namespace AKlump\FixtureFramework\Discovery;

use AKlump\FixtureFramework\Exception\FixtureException;
use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\FixtureInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

class FixtureDiscovery {

  private string $vendorDir;

  private bool $silent = true;

  public function __construct(string $vendor_dir) {
    $this->vendorDir = $vendor_dir;
  }

  public function setSilent(bool $silent): self {
    $this->silent = $silent;

    return $this;
  }

  public function discover(array $namespace_allow_list = []): array {
    $scanned_namespaces = [];
    $classes = $this->getCandidateClasses($scanned_namespaces);
    if (!$this->silent && !empty($scanned_namespaces)) {
      sort($scanned_namespaces);
      echo 'Scanning namespaces: ' . implode(', ', $scanned_namespaces) . PHP_EOL;
    }
    $fixtures = [];

    foreach ($classes as $class) {
      if (!empty($namespace_allow_list)) {
        $matched = false;
        foreach ($namespace_allow_list as $namespace) {
          if (str_starts_with($class, $namespace)) {
            $matched = true;
            break;
          }
        }
        if (!$matched) {
          continue;
        }
      }

      if (!class_exists($class)) {
        continue;
      }

      $reflection = new ReflectionClass($class);
      if (!$reflection->isInstantiable() || !$reflection->implementsInterface(FixtureInterface::class)) {
        continue;
      }

      $attributes = $reflection->getAttributes(Fixture::class);
      if (empty($attributes)) {
        continue;
      }

      /** @var Fixture $fixture_attribute */
      $fixture_attribute = $attributes[0]->newInstance();

      if (!$fixture_attribute->discoverable) {
        continue;
      }

      $id = trim($fixture_attribute->id);
      if ($id === '') {
        throw new FixtureException(sprintf('Fixture id must be a non-empty string on class "%s".', $class));
      }

      if (array_key_exists($id, $fixtures)) {
        throw new FixtureException(sprintf(
          'Duplicate fixture id "%s" found on class "%s" (already defined by "%s").',
          $id,
          $class,
          $fixtures[$id]['class']
        ));
      }

      $after = $this->normalizeStringList($fixture_attribute->after, 'after', $id, $class);
      $before = $this->normalizeStringList($fixture_attribute->before, 'before', $id, $class);
      $tags = $this->normalizeStringList($fixture_attribute->tags, 'tags', $id, $class, true);

      $fixtures[$id] = [
        'id' => $id,
        'class' => $class,
        'weight' => $fixture_attribute->weight,
        'after' => $after,
        'before' => $before,
        'tags' => $tags,
        'discoverable' => $fixture_attribute->discoverable,
        'namespace' => $reflection->getNamespaceName(),
      ];
    }

    return $fixtures;
  }

  private function normalizeStringList(array $value, string $field, string $id, string $class, bool $dedupe = false): array {
    foreach ($value as $i => $item) {
      if (!is_string($item)) {
        throw new FixtureException(sprintf(
          'Fixture "%s" (%s) has non-string value in "%s" at index %d.',
          $id,
          $class,
          $field,
          $i
        ));
      }
      $value[$i] = trim($item);
    }

    $value = array_values(array_filter($value, static fn(string $s) => $s !== ''));

    return $dedupe ? array_values(array_unique($value)) : $value;
  }

  private function getCandidateClasses(array &$scanned_namespaces = []): array {
    $classes = [];

    // 1. From classmap
    $classmap_file = $this->vendorDir . '/composer/autoload_classmap.php';
    if (file_exists($classmap_file)) {
      $classmap = require $classmap_file;
      $classes = array_merge($classes, array_keys($classmap));
    }

    // 2. From PSR-4
    $psr4_file = $this->vendorDir . '/composer/autoload_psr4.php';
    if (file_exists($psr4_file)) {
      $psr4 = require $psr4_file;
      foreach ($psr4 as $namespace => $dirs) {
        $scanned_namespaces[] = $namespace;
        foreach ($dirs as $dir) {
          if (is_dir($dir)) {
            $classes = array_merge($classes, $this->scanDirectoryForClasses($dir, $namespace));
          }
        }
      }
    }

    $scanned_namespaces = array_unique($scanned_namespaces);

    return array_unique($classes);
  }

  private function scanDirectoryForClasses(string $dir, string $namespace): array {
    $classes = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $php_files = new RegexIterator($iterator, '/\.php$/');

    foreach ($php_files as $file) {
      $relative_path = str_replace([$dir, '.php'], ['', ''], $file->getPathname());
      $class_name = $namespace . str_replace(DIRECTORY_SEPARATOR, '\\', ltrim($relative_path, DIRECTORY_SEPARATOR));
      $classes[] = $class_name;
    }

    return $classes;
  }
}
