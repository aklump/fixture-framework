<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\Runtime\RunContextStore;
use AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Runtime\RunContextStore
 * @uses \AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException
 */
class RunContextStoreTest extends TestCase {

  public function testSetAndGet() {
    $store = new RunContextStore();
    $store->set('key1', 'value1');
    $this->assertEquals('value1', $store->get('key1'));
  }

  public function testHas() {
    $store = new RunContextStore();
    $this->assertFalse($store->has('key1'));
    $store->set('key1', 'value1');
    $this->assertTrue($store->has('key1'));
  }

  public function testGetDefault() {
    $store = new RunContextStore();
    $this->assertNull($store->get('non_existent'));
    $this->assertEquals('default', $store->get('non_existent', 'default'));
  }

  public function testAll() {
    $store = new RunContextStore();
    $store->set('key1', 'value1');
    $store->set('key2', 'value2');
    $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $store->all());
  }

  public function testDuplicateKeyThrowsException() {
    $store = new RunContextStore();
    $store->set('key1', 'value1');
    $this->expectException(RunContextKeyAlreadyExistsException::class);
    $this->expectExceptionMessage('Run context key "key1" has already been set and cannot be overwritten.');
    $store->set('key1', 'value2');
  }

}
