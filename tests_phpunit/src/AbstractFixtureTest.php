<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\AbstractFixture;
use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\Exception\FixtureException;
use AKlump\FixtureFramework\Helper\FixtureInstantiator;
use AKlump\FixtureFramework\Runtime\RunContextStore;
use AKlump\FixtureFramework\Runtime\RunContextValidator;
use AKlump\FixtureFramework\Runtime\RunOptions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\AbstractFixture
 * @covers \AKlump\FixtureFramework\Fixture
 * @covers \AKlump\FixtureFramework\Traits\FixtureMetadataTrait
 * @covers \AKlump\FixtureFramework\Traits\FixtureOptionsTrait
 * @covers \AKlump\FixtureFramework\Traits\FixtureRunContextTrait
 * @covers \AKlump\FixtureFramework\Helper\GetFixtureIdByClassname
 * @covers \AKlump\FixtureFramework\Helper\FixtureInstantiator
 * @covers \AKlump\FixtureFramework\Runtime\RunContext
 * @covers \AKlump\FixtureFramework\Runtime\RunOptions
 * @covers \AKlump\FixtureFramework\Runtime\RunOptionsValidator
 * @uses \AKlump\FixtureFramework\AbstractFixture
 * @uses \AKlump\FixtureFramework\Exception\FixtureException
 */
class AbstractFixtureTest extends TestCase {

  public function testOnSuccessPrintsDoneByDefault() {
    $fixture = new class extends AbstractFixture {
      public function id(): string { return 'test'; }
      public function __invoke(): void {}
    };

    $this->expectOutputString("Done." . PHP_EOL);
    $fixture->onSuccess(FALSE);
  }

  public function testOnSuccessIsSilentWhenRequested() {
    $fixture = new class extends AbstractFixture {
      public function id(): string { return 'test'; }
      public function __invoke(): void {}
    };

    $this->expectOutputString("");
    $fixture->onSuccess(TRUE);
  }

  public function testOnFailureThrowsException() {
    $fixture = new class extends AbstractFixture {
      public function id(): string { return 'test'; }
      public function __invoke(): void {}
    };

    $e = new FixtureException("Test failure");
    $this->expectException(FixtureException::class);
    $this->expectExceptionMessage("Test failure");
    $fixture->onFailure($e, FALSE);
  }

  public function testMetadataTraitProperty() {
    $fixture = new class extends AbstractFixture {
      public function id(): string { return 'test'; }
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
      public function id(): string { return 'test'; }
      public function __invoke(): void {}
    };

    $this->assertTrue(property_exists($fixture, 'runContext'));
  }

  public function testOptionsTraitProperty() {
    $fixture = new class extends AbstractFixture {
      public function id(): string { return 'test'; }
      public function __invoke(): void {}
    };

    $this->assertTrue(property_exists($fixture, 'options'));
  }

  public function testMetadataTraitPropertyExists() {
    $fixture = new class extends AbstractFixture {
      public function id(): string { return 'test'; }
      public function __invoke(): void {}
    };

    $this->assertTrue(property_exists($fixture, 'fixture'));
  }

  public function testIdReturnsValueFromMetadata() {
    $instantiator = new FixtureInstantiator();
    $fixture = $instantiator(
      ['class' => Fixtures\FixtureA::class, 'id' => 'custom_id'],
      new RunOptions([]),
      $this->createMock(RunContextStore::class),
      $this->createMock(RunContextValidator::class)
    );
    $this->assertEquals('custom_id', $fixture->id());
  }

  public function testIdReturnsValueFromAttributeWhenMetadataMissing() {
    $instantiator = new FixtureInstantiator();
    $fixture = $instantiator(
      ['class' => Fixtures\FixtureA::class],
      new RunOptions([]),
      $this->createMock(RunContextStore::class),
      $this->createMock(RunContextValidator::class)
    );
    $this->assertEquals('fixture_a', $fixture->id());
  }

  public function testInstantiationThrowsWhenIdCannotBeResolved() {
    $instantiator = new FixtureInstantiator();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Fixture id must be a non-empty string');
    $instantiator(
      ['class' => \AKlump\FixtureFramework\Tests\Fixtures\MockFixture::class],
      new RunOptions([]),
      $this->createMock(RunContextStore::class),
      $this->createMock(RunContextValidator::class)
    );
  }
}
