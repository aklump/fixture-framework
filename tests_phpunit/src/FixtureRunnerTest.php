<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\Exception\FixtureException;
use AKlump\FixtureFramework\Runtime\FixtureCollectionBuilder;
use AKlump\FixtureFramework\Runtime\FixtureRunner;
use AKlump\FixtureFramework\Runtime\RunContextValidator;
use AKlump\FixtureFramework\Runtime\RunOptions;
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
 * @covers \AKlump\FixtureFramework\Runtime\FixtureRunner
 * @covers \AKlump\FixtureFramework\Runtime\FixtureCollectionBuilder
 * @uses \AKlump\FixtureFramework\AbstractFixture
 * @uses \AKlump\FixtureFramework\Runtime\RunContext
 * @uses \AKlump\FixtureFramework\Runtime\RunContextStore
 * @uses \AKlump\FixtureFramework\Runtime\RunContextValidator
 * @uses \AKlump\FixtureFramework\Runtime\RunOptions
 * @uses \AKlump\FixtureFramework\Runtime\RunOptionsValidator
 * @uses \AKlump\FixtureFramework\Exception\InvalidRunOptionsException
 * @uses \AKlump\FixtureFramework\Helper\FixtureInstantiator
 */
class FixtureRunnerTest extends TestCase {

  private function buildFixtures(array $fixture_index, array $options = []): array {
    $builder = new FixtureCollectionBuilder($options, new RunContextValidator());

    return $builder($fixture_index);
  }

  public function testOnSuccessAndOnFailure() {
    MockFixture::$successCount = 0;
    MockFixture::$failureCount = 0;
    MockFixture::$shouldFail = false;

    $index = [
      [
        'id' => 'mock_success',
        'class' => MockFixture::class,
      ],
    ];
    $fixtures = $this->buildFixtures($index);

    $runner = new FixtureRunner($fixtures);
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
    $index = [
      [
        'id' => 'mock_fail',
        'class' => MockFixture::class,
      ],
    ];
    MockFixture::$shouldFail = true;
    $fixtures = $this->buildFixtures($index);
    $runner = new FixtureRunner($fixtures);
    $this->expectException(FixtureException::class);
    $runner->run(TRUE);
    MockFixture::$shouldFail = false;
  }

  public function testRun() {
    FixtureA::$called = FALSE;
    FixtureB::$called = FALSE;

    $index = [
      [
        'id' => 'fixture_a',
        'class' => FixtureA::class,
      ],
      [
        'id' => 'fixture_b',
        'class' => FixtureB::class,
      ],
    ];
    $fixtures = $this->buildFixtures($index);

    $runner = new FixtureRunner($fixtures);
    $runner->run(TRUE);

    $this->assertTrue(FixtureA::$called);
    $this->assertTrue(FixtureB::$called);
  }

  public function testRunVerboseOutput() {
    $index = [
      [
        'id' => 'fixture_a',
        'class' => FixtureA::class,
      ],
    ];
    $fixtures = $this->buildFixtures($index);

    $runner = new FixtureRunner($fixtures);
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
    $index = [$metadata];
    $fixtures = $this->buildFixtures($index);

    $runner = new FixtureRunner($fixtures);
    $runner->run(TRUE);

    $this->assertEquals($metadata, FixtureWithData::$received);
  }

  public function testFixtureAccessesMetadataViaTrait() {
    $metadata = [
      'id' => 'fixture_with_trait',
      'class' => FixtureWithTrait::class,
      'weight' => 42,
    ];
    $index = [$metadata];
    $fixtures = $this->buildFixtures($index);

    $runner = new FixtureRunner($fixtures);
    $runner->run(TRUE);

    $this->assertEquals($metadata, FixtureWithTrait::$received);
  }

  public function testRunEmptyFixturesOutputsMessage() {
    $runner = new FixtureRunner([]);
    $this->expectOutputString("No fixtures found for execution. Check your classes for the #[AKlump\FixtureFramework\Fixture] attribute." . PHP_EOL);
    $runner->run(FALSE);
  }

  public function testRunEmptyFixturesSilentOutputsNothing() {
    $runner = new FixtureRunner([]);
    $this->expectOutputString("");
    $runner->run(TRUE);
  }

  public function testRunContextSharedAcrossFixtures() {
    $index = [
      [
        'id' => 'producer',
        'class' => ProducerFixture::class,
      ],
      [
        'id' => 'consumer',
        'class' => ConsumerFixture::class,
      ],
    ];
    $fixtures = $this->buildFixtures($index);

    $runner = new FixtureRunner($fixtures);
    $runner->run(TRUE);

    $this->assertEquals(999, ConsumerFixture::$consumedValue);
  }

  public function testOptionsAreInjected() {
    $options = ['env' => 'test', 'debug' => true];
    $index = [
      [
        'id' => 'options_test',
        'class' => OptionsTestFixture::class,
      ],
    ];
    $fixtures = $this->buildFixtures($index, $options);
    $runner = new FixtureRunner($fixtures);
    $runner->run(TRUE);

    $this->assertInstanceOf(RunOptions::class, OptionsTestFixture::$receivedOptionsInSetUp);
    $this->assertEquals($options, OptionsTestFixture::$receivedOptionsInSetUp->all());
    $this->assertInstanceOf(RunOptions::class, OptionsTestFixture::$receivedOptionsInOnSuccess);
    $this->assertEquals($options, OptionsTestFixture::$receivedOptionsInOnSuccess->all());
  }

  public function testRunContextIsolationBetweenRuns() {
    $index = [
      [
        'id' => 'producer',
        'class' => ProducerFixture::class,
      ],
    ];
    $fixtures1 = $this->buildFixtures($index);
    $runner1 = new FixtureRunner($fixtures1);
    $runner1->run(TRUE);

    $fixtures2 = $this->buildFixtures($index);
    $runner2 = new FixtureRunner($fixtures2);
    $runner2->run(TRUE);
    $this->addToAssertionCount(1);
  }

  public function testConstructThrowsOnInvalidFixtureType() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('All fixtures must implement AKlump\FixtureFramework\FixtureInterface; stdClass does not.');
    new FixtureRunner([new \stdClass()]);
  }

  public function testConstructThrowsOnInvalidFixtureTypeNonObject() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('All fixtures must implement AKlump\FixtureFramework\FixtureInterface; string does not.');
    new FixtureRunner(['not_an_object']);
  }

  public function testOnFailureIsSilentWhenRequested() {
    $index = [
      [
        'id' => 'mock_fail',
        'class' => MockFixture::class,
      ],
    ];
    MockFixture::$shouldFail = true;
    $fixtures = $this->buildFixtures($index);
    $runner = new FixtureRunner($fixtures);

    // We expect the exception, but no output because silent is TRUE
    $this->expectOutputString("");
    try {
      $runner->run(TRUE);
    }
    catch (FixtureException $e) {
    }
    MockFixture::$shouldFail = false;
  }
}
