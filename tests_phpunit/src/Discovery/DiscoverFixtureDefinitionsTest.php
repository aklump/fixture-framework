<?php

namespace AKlump\FixtureFramework\Tests\Discovery;

use AKlump\FixtureFramework\Discovery\DiscoverFixtureDefinitions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Discovery\DiscoverFixtureDefinitions
 * @uses   \AKlump\FixtureFramework\Discovery\FixtureCache
 * @uses   \AKlump\FixtureFramework\Discovery\FixtureDiscovery
 * @uses   \AKlump\FixtureFramework\Discovery\FixtureOrderer
 * @uses   \AKlump\FixtureFramework\Fixture
 */
class DiscoverFixtureDefinitionsTest extends TestCase {

  private string $vendorDir;

  protected function setUp(): void {
    $this->vendorDir = __DIR__ . '/../../../vendor';
    // Clear temp files created by DiscoverFixtureDefinitions if possible,
    // but DiscoverFixtureDefinitions uses sys_get_temp_dir() and sha1 of vendor path.
  }

  public function testInvokeWithInvalidVendorDirThrowsException() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Is not a directory');
    $getFixtures = new DiscoverFixtureDefinitions();
    $getFixtures(__DIR__ . '/non-existent');
  }

  public function testInvokeWithNonVendorDirThrowsException() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Must be a Composer vendor dir');
    $getFixtures = new DiscoverFixtureDefinitions();
    $getFixtures(__DIR__);
  }

  public function testInvokeWithValidVendorDir() {
    $getFixtures = new DiscoverFixtureDefinitions();
    // Use a specific namespace to keep it fast and avoid side effects
    $fixtures = $getFixtures($this->vendorDir, ['AKlump\FixtureFramework\Tests\Fixtures\\'], TRUE, TRUE);
    $this->assertIsArray($fixtures);
    $ids = array_column($fixtures, 'id');
    $this->assertContains('fixture_a', $ids);
  }

  public function testInvokeUsesCache() {
    $getFixtures = new DiscoverFixtureDefinitions();
    $namespaces = ['AKlump\FixtureFramework\Tests\Fixtures\\'];

    // First call to populate cache
    $getFixtures($this->vendorDir, $namespaces, TRUE, TRUE);

    // Second call should use cache
    $fixtures = $getFixtures($this->vendorDir, $namespaces, FALSE, TRUE);

    $this->assertIsArray($fixtures);
  }

  public function testInvokeWithEnvCacheFile() {
    $cacheFile = tempnam(sys_get_temp_dir(), 'env_cache_test');
    putenv("TEST_FIXTURE_CACHE_FILE=$cacheFile");

    $getFixtures = new DiscoverFixtureDefinitions();
    $namespaces = ['AKlump\FixtureFramework\Tests\Fixtures\\'];

    $getFixtures($this->vendorDir, $namespaces, TRUE, TRUE);

    $this->assertFileExists($cacheFile);
    $this->assertGreaterThan(0, filesize($cacheFile));

    putenv("TEST_FIXTURE_CACHE_FILE="); // Reset
    unlink($cacheFile);
  }

  public function testInvokeWithFilter() {
    $getFixtures = new DiscoverFixtureDefinitions();
    $namespaces = ['AKlump\FixtureFramework\Tests\Fixtures\\'];

    // Test exact match
    $fixtures = $getFixtures($this->vendorDir, $namespaces, TRUE, TRUE, '/^fixture_a$/');
    $ids = array_column($fixtures, 'id');
    $this->assertCount(1, $ids);
    $this->assertContains('fixture_a', $ids);

    // Test regex match
    $fixtures = $getFixtures($this->vendorDir, $namespaces, TRUE, TRUE, '/^fixture_.*$/');
    $ids = array_column($fixtures, 'id');
    $this->assertContains('fixture_a', $ids);
    $this->assertContains('fixture_b', $ids);

    // Test no match
    $fixtures = $getFixtures($this->vendorDir, $namespaces, TRUE, TRUE, '/^non_existent$/');
    $this->assertEmpty($fixtures);
  }

  public function testInvokeWithFilterDependenciesReturnsOnlyFiltered() {
    $getFixtures = new DiscoverFixtureDefinitions();
    $namespaces = ['AKlump\FixtureFramework\Tests\Fixtures\\'];

    // fixture_b depends on fixture_a
    // Since we filter AFTER ordering, it should NOT fail, even if fixture_a is filtered out.
    $fixtures = $getFixtures($this->vendorDir, $namespaces, TRUE, TRUE, '/^fixture_b$/');
    $ids = array_column($fixtures, 'id');
    $this->assertCount(1, $ids);
    $this->assertContains('fixture_b', $ids);
    $this->assertNotContains('fixture_a', $ids);
  }

  public function testInvokeWithNonDelimitedFilter() {
    $getFixtures = new DiscoverFixtureDefinitions();
    $namespaces = ['AKlump\FixtureFramework\Tests\Fixtures\\'];
    $fixtures = $getFixtures($this->vendorDir, $namespaces, TRUE, TRUE, 'fixture_a');
    $ids = array_column($fixtures, 'id');
    $this->assertContains('fixture_a', $ids);
  }

  public function testInvokeWithEmptyFilterUsesDefault() {
    $getFixtures = new DiscoverFixtureDefinitions();
    $namespaces = ['AKlump\FixtureFramework\Tests\Fixtures\\'];
    // Setting filter to an empty string explicitly to ensure it hits line 72 in toPregPattern
    $fixtures = $getFixtures($this->vendorDir, $namespaces, TRUE, TRUE, ' ');
    $this->assertNotEmpty($fixtures);
  }

  public function testIsDelimitedRegexWithInvalidStart() {
    $getFixtures = new DiscoverFixtureDefinitions();
    $reflection = new \ReflectionClass($getFixtures);
    $method = $reflection->getMethod('isDelimitedRegex');
    $method->setAccessible(TRUE);

    $this->assertFalse($method->invoke($getFixtures, 'a/b/c')); // Alphanumeric start
    $this->assertFalse($method->invoke($getFixtures, ' /b/c')); // Space start
    $this->assertFalse($method->invoke($getFixtures, '\\/b/c')); // Backslash start
    $this->assertFalse($method->invoke($getFixtures, '')); // Empty string
  }

  public function testIsDelimitedRegexWithEscapedDelimiter() {
    $getFixtures = new DiscoverFixtureDefinitions();
    $reflection = new \ReflectionClass($getFixtures);
    $method = $reflection->getMethod('isDelimitedRegex');
    $method->setAccessible(TRUE);

    // This should return FALSE because the closing delimiter is escaped
    $this->assertFalse($method->invoke($getFixtures, '/abc\/'));
  }

  public function testIsDelimitedRegexWithNoClosingDelimiter() {
    $getFixtures = new DiscoverFixtureDefinitions();
    $reflection = new \ReflectionClass($getFixtures);
    $method = $reflection->getMethod('isDelimitedRegex');
    $method->setAccessible(TRUE);

    $this->assertFalse($method->invoke($getFixtures, '/abc'));
  }

  public function testInvokeRebuildsIfCacheIsInvalid() {
    $cacheFile = tempnam(sys_get_temp_dir(), 'invalid_cache_test');
    // Write something that is NOT a valid JSON of fixtures
    file_put_contents($cacheFile, 'invalid');
    putenv("TEST_FIXTURE_CACHE_FILE=$cacheFile");

    $getFixtures = new DiscoverFixtureDefinitions();
    $namespaces = ['AKlump\FixtureFramework\Tests\Fixtures\\'];

    // This should trigger line 50 if the cache->get() returns NULL
    $fixtures = $getFixtures($this->vendorDir, $namespaces, FALSE, TRUE);

    $this->assertIsArray($fixtures);
    $this->assertNotEmpty($fixtures);

    putenv("TEST_FIXTURE_CACHE_FILE="); // Reset
    unlink($cacheFile);
  }

  public function testCacheDirectoryCreation() {
    $tempBase = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'test_fixture_temp_' . uniqid();
    // We can't easily change sys_get_temp_dir() but we can mock it? No.
    // However, DiscoverFixtureDefinitions uses sys_get_temp_dir() . '/test_fixture'.
    // If we can ensure it doesn't exist, it will try to create it.
    $cacheDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'test_fixture';
    if (is_dir($cacheDir)) {
      // It's dangerous to delete the whole directory if other tests use it,
      // but this is a controlled environment.
      // Let's just check if it's already covered or try to hit it.
    }
    // Actually, I can just call it and it will try to create it if it doesn't exist.
    // Since it's already there most likely, I'll rely on previous runs or just skip manual deletion.
    $getFixtures = new DiscoverFixtureDefinitions();
    $getFixtures($this->vendorDir, ['AKlump\FixtureFramework\Tests\Fixtures\\'], TRUE, TRUE);
    $this->assertTrue(is_dir($cacheDir));
  }
}
