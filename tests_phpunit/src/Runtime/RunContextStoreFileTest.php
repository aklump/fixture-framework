<?php

namespace AKlump\FixtureFramework\Tests\Runtime;

use AKlump\FixtureFramework\Runtime\RunContextStoreFile;
use AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Runtime\RunContextStoreFile
 * @uses \AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException
 */
class RunContextStoreFileTest extends TestCase {

  private string $filePath;

  protected function setUp(): void {
    $this->filePath = tempnam(sys_get_temp_dir(), 'test_file_');
  }

  protected function tearDown(): void {
    if (file_exists($this->filePath)) {
      unlink($this->filePath);
    }
  }

  public function testSetAndGet() {
    $store = new RunContextStoreFile($this->filePath);
    $store->set('key1', 'value1');
    $this->assertEquals('value1', $store->get('key1'));

    // Test persistence and caching
    $store_two = new RunContextStoreFile($this->filePath);
    $this->assertEquals('value1', $store_two->get('key1'));
  }

  public function testHas() {
    $store = new RunContextStoreFile($this->filePath);
    $this->assertFalse($store->has('key1'));
    $store->set('key1', 'value1');
    $this->assertTrue($store->has('key1'));
  }

  public function testGetDefault() {
    $store = new RunContextStoreFile($this->filePath);
    $this->assertNull($store->get('non_existent'));
    $this->assertEquals('default', $store->get('non_existent', 'default'));
  }

  public function testAll() {
    $store = new RunContextStoreFile($this->filePath);
    $store->set('key1', 'value1');
    $store->set('key2', 'value2');
    $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $store->all());
  }

  public function testDuplicateKeyThrowsException() {
    $store = new RunContextStoreFile($this->filePath);
    $store->set('key1', 'value1');
    $this->expectException(RunContextKeyAlreadyExistsException::class);
    $store->set('key1', 'value2');
  }

  public function testMixedValues() {
    $store = new RunContextStoreFile($this->filePath);
    $data = ['foo' => 'bar', 'baz' => 123];
    $store->set('key1', $data);
    $this->assertEquals($data, $store->get('key1'));
  }

}
