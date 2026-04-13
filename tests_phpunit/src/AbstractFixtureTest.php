<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Exception\FixtureException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\AbstractFixture
 * @covers \AKlump\FixtureFramework\FixtureMetadataTrait
 * @covers \AKlump\FixtureFramework\FixtureOptionsTrait
 * @covers \AKlump\FixtureFramework\FixtureRunContextTrait
 * @uses \AKlump\FixtureFramework\AbstractFixture
 * @uses \AKlump\FixtureFramework\Exception\FixtureException
 */
class AbstractFixtureTest extends TestCase {

  public function testOnSuccessPrintsDoneByDefault() {
    $fixture = new class extends AbstractFixture {
      public function __invoke(): void {}
    };

    $this->expectOutputString("Done." . PHP_EOL);
    $fixture->onSuccess(FALSE);
  }

  public function testOnSuccessIsSilentWhenRequested() {
    $fixture = new class extends AbstractFixture {
      public function __invoke(): void {}
    };

    $this->expectOutputString("");
    $fixture->onSuccess(TRUE);
  }

  public function testOnFailureThrowsException() {
    $fixture = new class extends AbstractFixture {
      public function __invoke(): void {}
    };

    $e = new FixtureException("Test failure");
    $this->expectException(FixtureException::class);
    $this->expectExceptionMessage("Test failure");
    $fixture->onFailure($e, FALSE);
  }

  public function testMetadataTraitProperty() {
    $fixture = new class extends AbstractFixture {
      public function __invoke(): void {}
      public function getFixture() { return $this->fixture; }
      public function setFixture(array $f) { $this->fixture = $f; }
    };

    $data = ['id' => 'foo'];
    $fixture->setFixture($data);
    $this->assertEquals($data, $fixture->getFixture());
  }

  public function testRunContextTraitProperty() {
    $fixture = new class extends AbstractFixture {
      public function __invoke(): void {}
    };

    $this->assertTrue(property_exists($fixture, 'runContext'));
  }

  public function testOptionsTraitProperty() {
    $fixture = new class extends AbstractFixture {
      public function __invoke(): void {}
    };

    $this->assertTrue(property_exists($fixture, 'options'));
  }

  public function testMetadataTraitPropertyExists() {
    $fixture = new class extends AbstractFixture {
      public function __invoke(): void {}
    };

    $this->assertTrue(property_exists($fixture, 'fixture'));
  }
}
