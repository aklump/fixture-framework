<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\Discovery\FixtureCache;
use AKlump\FixtureFramework\Discovery\FixtureDiscovery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Discovery\FixtureCache
 * @uses   \AKlump\FixtureFramework\Discovery\FixtureDiscovery
 */
class FixtureCacheTest extends TestCase {

  private string $cacheFile;

  private string $vendorDir;

  protected function setUp(): void {
    $this->cacheFile = tempnam(sys_get_temp_dir(), 'fixture_cache_test');
    $this->vendorDir = __DIR__ . '/../../vendor';
  }

  protected function tearDown(): void {
    if (file_exists($this->cacheFile)) {
      unlink($this->cacheFile);
    }
  }

  public function testGetReturnsNullIfFileDoesNotExist() {
    unlink($this->cacheFile);
    $cache = new FixtureCache($this->cacheFile, $this->vendorDir);
    $this->assertNull($cache->get());
  }

  public function testSetAndGet() {
    $cache = new FixtureCache($this->cacheFile, $this->vendorDir);
    $data = ['foo' => ['id' => 'foo']];
    $cache->set($data);
    $this->assertEquals($data, $cache->get());
  }

  public function testGetReturnsNullIfInvalidatedByPsr4() {
    $cache = new FixtureCache($this->cacheFile, $this->vendorDir);
    $cache->set(['foo' => ['id' => 'foo']]);

    // Backdate the cache file
    touch($this->cacheFile, filemtime($this->vendorDir . '/composer/autoload_psr4.php') - 1);

    $this->assertNull($cache->get());
  }

  public function testGetReturnsNullIfInvalidatedByClassmap() {
    $cache = new FixtureCache($this->cacheFile, $this->vendorDir);
    $cache->set(['foo' => ['id' => 'foo']]);

    // Backdate the cache file
    touch($this->cacheFile, filemtime($this->vendorDir . '/composer/autoload_classmap.php') - 1);

    $this->assertNull($cache->get());
  }

  public function testRebuild() {
    $discovery = $this->createMock(FixtureDiscovery::class);
    $data = ['bar' => ['id' => 'bar']];
    $discovery->expects($this->once())
      ->method('discover')
      ->willReturn($data);

    $cache = new FixtureCache($this->cacheFile, $this->vendorDir);
    $result = $cache->rebuild($discovery);

    $this->assertEquals($data, $result);
    $this->assertEquals($data, $cache->get());
  }
}
