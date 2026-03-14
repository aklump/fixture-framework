<?php

namespace AKlump\TestFixture\Tests;

use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Exception\FixtureException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\TestFixture\AbstractFixture
 * @covers \AKlump\TestFixture\FixtureMetadataTrait
 */
class AbstractFixtureTest extends TestCase {

  public function testOnSuccessPrintsDoneByDefault() {
    $fixture = new class extends AbstractFixture {
      public function setUp(array $options): void {}
    };

    $this->expectOutputString("Done." . PHP_EOL);
    $fixture->onSuccess(FALSE);
  }

  public function testOnSuccessIsSilentWhenRequested() {
    $fixture = new class extends AbstractFixture {
      public function setUp(array $options): void {}
    };

    $this->expectOutputString("");
    $fixture->onSuccess(TRUE);
  }

  public function testOnFailureThrowsException() {
    $fixture = new class extends AbstractFixture {
      public function setUp(array $options): void {}
    };

    $e = new FixtureException("Test failure");
    $this->expectException(FixtureException::class);
    $this->expectExceptionMessage("Test failure");
    $fixture->onFailure($e, FALSE);
  }

  public function testMetadataTraitProperty() {
    $fixture = new class extends AbstractFixture {
      public function setUp(array $options): void {}
      public function getFixture() { return $this->fixture; }
      public function setFixture(array $f) { $this->fixture = $f; }
    };

    $data = ['id' => 'foo'];
    $fixture->setFixture($data);
    $this->assertEquals($data, $fixture->getFixture());
  }

  public function testRunContextTraitProperty() {
    $fixture = new class extends AbstractFixture {
      public function setUp(array $options): void {}
    };

    $this->assertTrue(property_exists($fixture, 'runContext'));
  }
}
