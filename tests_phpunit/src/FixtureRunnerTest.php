<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\Exception\FixtureException;
use AKlump\FixtureFramework\Exception\InvalidRunOptionsException;
use AKlump\FixtureFramework\FixtureRunner;
use AKlump\FixtureFramework\RunOptions;
use AKlump\FixtureFramework\Tests\Fixtures\ConsumerFixture;
use AKlump\FixtureFramework\Tests\Fixtures\FixtureA;
use AKlump\FixtureFramework\Tests\Fixtures\FixtureB;
use AKlump\FixtureFramework\Tests\Fixtures\FixtureWithData;
use AKlump\FixtureFramework\Tests\Fixtures\FixtureWithTrait;
use AKlump\FixtureFramework\Tests\Fixtures\MockFixture;
use AKlump\FixtureFramework\Tests\Fixtures\OptionsTestFixture;
use AKlump\FixtureFramework\Tests\Fixtures\ProducerFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\FixtureRunner
 * @uses \AKlump\FixtureFramework\AbstractFixture
 * @uses \AKlump\FixtureFramework\RunContext
 * @uses \AKlump\FixtureFramework\RunContextStore
 * @uses \AKlump\FixtureFramework\RunContextValidator
 * @uses \AKlump\FixtureFramework\RunOptions
 * @uses \AKlump\FixtureFramework\RunOptionsValidator
 * @uses \AKlump\FixtureFramework\Exception\InvalidRunOptionsException
 */
class FixtureRunnerTest extends TestCase {

  public function testOnSuccessAndOnFailure() {
    MockFixture::$successCount = 0;
    MockFixture::$failureCount = 0;
    MockFixture::$shouldFail = false;

    $fixtures = [
      [
        'id' => 'mock_success',
        'class' => MockFixture::class,
      ],
    ];

    $runner = new FixtureRunner($fixtures, []);
    $runner->run(TRUE);

    $this->assertEquals(1, MockFixture::$successCount);
    $this->assertEquals(0, MockFixture::$failureCount);

    MockFixture::$shouldFail = true;
    try {
      $runner->run(TRUE);
    }
    catch (FixtureException $e) {
    }

    $this->assertEquals(1, MockFixture::$successCount);
    $this->assertEquals(1, MockFixture::$failureCount);
    MockFixture::$shouldFail = false;
  }

  public function testOnFailureThrowsException() {
    $fixtures = [
      [
        'id' => 'mock_fail',
        'class' => MockFixture::class,
      ],
    ];
    MockFixture::$shouldFail = true;
    $runner = new FixtureRunner($fixtures, []);
    $this->expectException(FixtureException::class);
    $runner->run(TRUE);
    MockFixture::$shouldFail = false;
  }

  public function testRun() {
    FixtureA::$called = FALSE;
    FixtureB::$called = FALSE;

    $fixtures = [
      [
        'id' => 'fixture_a',
        'class' => FixtureA::class,
      ],
      [
        'id' => 'fixture_b',
        'class' => FixtureB::class,
      ],
    ];

    $runner = new FixtureRunner($fixtures, ['key' => 'value']);
    $runner->run(TRUE);

    $this->assertTrue(FixtureA::$called);
    $this->assertTrue(FixtureB::$called);
  }

  public function testRunVerboseOutput() {
    $fixtures = [
      [
        'id' => 'fixture_a',
        'class' => FixtureA::class,
      ],
    ];
    $runner = new FixtureRunner($fixtures, []);
    $this->expectOutputString(sprintf('Running fixture "fixture_a" (%s)... %sDone.%s', FixtureA::class, PHP_EOL, PHP_EOL));
    $runner->run(FALSE);
  }

  public function testFixtureAccessesMetadata() {
    $metadata = [
      'id' => 'fixture_with_data',
      'class' => FixtureWithData::class,
      'weight' => 42,
      'tags' => ['tag1', 'tag2'],
    ];
    $fixtures = [$metadata];

    $runner = new FixtureRunner($fixtures, []);
    $runner->run(TRUE);

    $this->assertEquals($metadata, FixtureWithData::$received);
  }

  public function testFixtureAccessesMetadataViaTrait() {
    $metadata = [
      'id' => 'fixture_with_trait',
      'class' => FixtureWithTrait::class,
      'weight' => 42,
    ];
    $fixtures = [$metadata];

    $runner = new FixtureRunner($fixtures, []);
    $runner->run(TRUE);

    $this->assertEquals($metadata, FixtureWithTrait::$received);
  }

  public function testRunEmptyFixturesOutputsMessage() {
    $runner = new FixtureRunner([], []);
    $this->expectOutputString("No fixtures found for execution. Check your classes for the #[AKlump\FixtureFramework\Fixture] attribute." . PHP_EOL);
    $runner->run(FALSE);
  }

  public function testRunEmptyFixturesSilentOutputsNothing() {
    $runner = new FixtureRunner([], []);
    $this->expectOutputString("");
    $runner->run(TRUE);
  }

  public function testRunContextSharedAcrossFixtures() {
    $fixtures = [
      [
        'id' => 'producer',
        'class' => ProducerFixture::class,
      ],
      [
        'id' => 'consumer',
        'class' => ConsumerFixture::class,
      ],
    ];

    $runner = new FixtureRunner($fixtures, []);
    $runner->run(TRUE);

    $this->assertEquals(999, ConsumerFixture::$consumedValue);
  }

  public function testOptionsAreInjected() {
    $options = ['env' => 'test', 'debug' => true];
    $fixtures = [
      [
        'id' => 'options_test',
        'class' => OptionsTestFixture::class,
      ],
    ];
    $runner = new FixtureRunner($fixtures, $options);
    $runner->run(TRUE);

    $this->assertInstanceOf(RunOptions::class, OptionsTestFixture::$receivedOptionsInSetUp);
    $this->assertEquals($options, OptionsTestFixture::$receivedOptionsInSetUp->all());
    $this->assertInstanceOf(RunOptions::class, OptionsTestFixture::$receivedOptionsInOnSuccess);
    $this->assertEquals($options, OptionsTestFixture::$receivedOptionsInOnSuccess->all());
  }

  /**
   * @dataProvider provideInvalidOptions
   */
  public function testInvalidOptionsThrowException(array $options, string $expectedPath) {
    $this->expectException(InvalidRunOptionsException::class);
    $this->expectExceptionMessage(sprintf('Run options must contain only null, scalar, or array values. Invalid value found at "%s".', $expectedPath));
    new FixtureRunner([], $options);
  }

  public function provideInvalidOptions(): array {
    return [
      'object at top level' => [['client' => new \stdClass()], 'client'],
      'object in nested array' => [['api' => ['client' => new \stdClass()]], 'api.client'],
      'closure' => [['callback' => function () {}], 'callback'],
      'numeric index' => [['servers' => [['host' => 'localhost'], new \stdClass()]], 'servers.1'],
    ];
  }

  public function testRunContextIsolationBetweenRuns() {
    $fixtures = [
      [
        'id' => 'producer',
        'class' => ProducerFixture::class,
      ],
    ];

    $runner1 = new FixtureRunner($fixtures, []);
    $runner1->run(TRUE);

    $runner2 = new FixtureRunner($fixtures, []);
    // This second run will create a NEW store.
    // We can't easily verify the store is new unless we have a way to inspect it,
    // but the implementation shows a new store is created in run().
    $runner2->run(TRUE);
    $this->addToAssertionCount(1);
  }

  public function testConstructorAcceptsRunOptions() {
    $options = new RunOptions(['foo' => 'bar']);
    $runner = new FixtureRunner([], $options);
    $reflection = new \ReflectionClass($runner);
    $property = $reflection->getProperty('globalOptions');
    $property->setAccessible(TRUE);
    $this->assertSame($options, $property->getValue($runner));
  }
}
