<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\Fixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Fixture
 * @uses \AKlump\FixtureFramework\Fixture
 */
class FixtureAttributeTest extends TestCase {

  public function testConstructor() {
    $fixture = new Fixture(
      id: 'test_id',
      description: 'Test description',
      weight: 10,
      after: ['a'],
      before: ['b'],
      tags: ['tag'],
      discoverable: false
    );

    $this->assertEquals('test_id', $fixture->id);
    $this->assertEquals('Test description', $fixture->description);
    $this->assertEquals(10, $fixture->weight);
    $this->assertEquals(['a'], $fixture->after);
    $this->assertEquals(['b'], $fixture->before);
    $this->assertEquals(['tag'], $fixture->tags);
    $this->assertFalse($fixture->discoverable);
  }

  public function testEmptyDescriptionByDefault() {
    $fixture = new Fixture(id: 'test_id');
    $this->assertSame('', $fixture->description);
  }
}
