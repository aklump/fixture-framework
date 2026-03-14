<?php

namespace AKlump\TestFixture\Tests;

use AKlump\TestFixture\Helper\GetFixtures;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\TestFixture\Helper\GetFixtures
 * @uses \AKlump\TestFixture\FixtureCache
 * @uses \AKlump\TestFixture\FixtureDiscovery
 * @uses \AKlump\TestFixture\FixtureOrderer
 * @uses \AKlump\TestFixture\Fixture
 */
class GetFixturesTest extends TestCase {

  private string $vendorDir;

  protected function setUp(): void {
    $this->vendorDir = __DIR__ . '/../../vendor';
    // Clear temp files created by GetFixtures if possible,
    // but GetFixtures uses sys_get_temp_dir() and sha1 of vendor path.
  }

  public function testInvokeWithInvalidVendorDirThrowsException() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Is not a directory');
    $getFixtures = new GetFixtures();
    $getFixtures(__DIR__ . '/non-existent');
  }

  public function testInvokeWithNonVendorDirThrowsException() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Must be a Composer vendor dir');
    $getFixtures = new GetFixtures();
    $getFixtures(__DIR__);
  }

  public function testInvokeWithValidVendorDir() {
    $getFixtures = new GetFixtures();
    // Use a specific namespace to keep it fast and avoid side effects
    $fixtures = $getFixtures($this->vendorDir, ['AKlump\TestFixture\Tests\Fixtures\\'], TRUE);
    $this->assertIsArray($fixtures);
    $ids = array_column($fixtures, 'id');
    $this->assertContains('fixture_a', $ids);
  }

  public function testInvokeUsesCache() {
    $getFixtures = new GetFixtures();
    $namespaces = ['AKlump\TestFixture\Tests\Fixtures\\'];

    // First call to populate cache
    $getFixtures($this->vendorDir, $namespaces, TRUE);

    // Second call should use cache
    $fixtures = $getFixtures($this->vendorDir, $namespaces, FALSE);

    $this->assertIsArray($fixtures);
  }

  public function testInvokeWithEnvCacheFile() {
    $cacheFile = tempnam(sys_get_temp_dir(), 'env_cache_test');
    putenv("TEST_FIXTURE_CACHE_FILE=$cacheFile");

    $getFixtures = new GetFixtures();
    $namespaces = ['AKlump\TestFixture\Tests\Fixtures\\'];

    $getFixtures($this->vendorDir, $namespaces, TRUE);

    $this->assertFileExists($cacheFile);
    $this->assertGreaterThan(0, filesize($cacheFile));

    putenv("TEST_FIXTURE_CACHE_FILE="); // Reset
    unlink($cacheFile);
  }

  public function testInvokeWithFilter() {
    $getFixtures = new GetFixtures();
    $namespaces = ['AKlump\TestFixture\Tests\Fixtures\\'];

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
    $getFixtures = new GetFixtures();
    $namespaces = ['AKlump\TestFixture\Tests\Fixtures\\'];

    // fixture_b depends on fixture_a
    // Since we filter AFTER ordering, it should NOT fail, even if fixture_a is filtered out.
    $fixtures = $getFixtures($this->vendorDir, $namespaces, TRUE, TRUE, '/^fixture_b$/');
    $ids = array_column($fixtures, 'id');
    $this->assertCount(1, $ids);
    $this->assertContains('fixture_b', $ids);
    $this->assertNotContains('fixture_a', $ids);
  }
}
