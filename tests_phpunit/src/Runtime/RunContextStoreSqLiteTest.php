<?php

namespace AKlump\FixtureFramework\Tests\Runtime;

use AKlump\FixtureFramework\Runtime\RunContextStoreSqLite;
use AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Runtime\RunContextStoreSqLite
 * @uses \AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException
 */
class RunContextStoreSqLiteTest extends TestCase {

  private string $dbPath;

  protected function setUp(): void {
    $this->dbPath = tempnam(sys_get_temp_dir(), 'test_sqlite_');
  }

  protected function tearDown(): void {
    if (file_exists($this->dbPath)) {
      unlink($this->dbPath);
    }
  }

  public function testSetAndGet() {
    $store = new RunContextStoreSqLite($this->dbPath);
    $store->set('key1', 'value1');
    $this->assertEquals('value1', $store->get('key1'));

    // Test persistence and caching
    $store_two = new RunContextStoreSqLite($this->dbPath);
    $this->assertEquals('value1', $store_two->get('key1'));
  }

  public function testCustomTableName() {
    $table_name = 'custom_table';
    $store = new RunContextStoreSqLite($this->dbPath, $table_name);
    $store->set('key1', 'value1');
    $this->assertEquals('value1', $store->get('key1'));

    // Verify the table actually exists in the database
    $pdo = new \PDO("sqlite:$this->dbPath");
    $statement = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table_name'");
    $this->assertNotFalse($statement->fetch());
  }

  public function testHas() {
    $store = new RunContextStoreSqLite($this->dbPath);
    $this->assertFalse($store->has('key1'));
    $store->set('key1', 'value1');
    $this->assertTrue($store->has('key1'));
  }

  public function testGetDefault() {
    $store = new RunContextStoreSqLite($this->dbPath);
    $this->assertNull($store->get('non_existent'));
    $this->assertEquals('default', $store->get('non_existent', 'default'));
  }

  public function testAll() {
    $store = new RunContextStoreSqLite($this->dbPath);
    $store->set('key1', 'value1');
    $store->set('key2', 'value2');
    $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $store->all());
  }

  public function testDuplicateKeyThrowsException() {
    $store = new RunContextStoreSqLite($this->dbPath);
    $store->set('key1', 'value1');
    $this->expectException(RunContextKeyAlreadyExistsException::class);
    $store->set('key1', 'value2');
  }

  public function testMixedValues() {
    $store = new RunContextStoreSqLite($this->dbPath);
    $data = ['foo' => 'bar', 'baz' => 123];
    $store->set('key1', $data);
    $this->assertEquals($data, $store->get('key1'));
  }

  public function testRemove() {
    $store = new RunContextStoreSqLite($this->dbPath);
    $store->set('key1', 'value1');
    $this->assertTrue($store->has('key1'));
    $store->remove('key1');
    $this->assertFalse($store->has('key1'));
    $this->assertNull($store->get('key1'));

    // Test persistence
    $store_two = new RunContextStoreSqLite($this->dbPath);
    $this->assertFalse($store_two->has('key1'));
  }

}
