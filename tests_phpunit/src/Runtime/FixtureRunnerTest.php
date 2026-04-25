<?php

namespace AKlump\FixtureFramework\Tests\Runtime;

use AKlump\FixtureFramework\Exception\FixtureException;
use AKlump\FixtureFramework\FixtureInterface;
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
 * @uses \AKlump\FixtureFramework\Runtime\FixtureInstantiator
 */
class FixtureRunnerTest extends TestCase {

  private function buildFixtures(array $fixture_index, array $options = []): array {
    $instantiator = new \AKlump\FixtureFramework\Runtime\FixtureInstantiator($options, new RunContextValidator());
    $builder = new FixtureCollectionBuilder($instantiator);

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

  public function testRunInWorkingDirectory() {
    $dir = sys_get_temp_dir() . '/fixture_test_' . uniqid();
    mkdir($dir);
    $real_dir = realpath($dir);

    $fixture = $this->createMock(FixtureInterface::class);
    $fixture->method('id')->willReturn('test_fixture');

    $captured_dir = null;
    $fixture->expects($this->once())
      ->method('__invoke')
      ->willReturnCallback(function () use (&$captured_dir) {
        $captured_dir = getcwd();
      });

    $runner = new FixtureRunner([$fixture]);
    $runner->run(TRUE, $dir);

    $this->assertEquals($real_dir, realpath($captured_dir));
    rmdir($dir);
  }

  public function testRunMultipleFixturesInWorkingDirectory() {
    $dir = sys_get_temp_dir() . '/fixture_test_' . uniqid();
    mkdir($dir);
    $real_dir = realpath($dir);

    $fixture1 = $this->createMock(FixtureInterface::class);
    $fixture1->method('id')->willReturn('fixture1');
    $captured_dir1 = null;
    $fixture1->expects($this->once())
      ->method('__invoke')
      ->willReturnCallback(function () use (&$captured_dir1) {
        $captured_dir1 = getcwd();
      });

    $fixture2 = $this->createMock(FixtureInterface::class);
    $fixture2->method('id')->willReturn('fixture2');
    $captured_dir2 = null;
    $fixture2->expects($this->once())
      ->method('__invoke')
      ->willReturnCallback(function () use (&$captured_dir2) {
        $captured_dir2 = getcwd();
      });

    $runner = new FixtureRunner([$fixture1, $fixture2]);
    $runner->run(TRUE, $dir);

    $this->assertEquals($real_dir, realpath($captured_dir1));
    $this->assertEquals($real_dir, realpath($captured_dir2));

    rmdir($dir);
  }

  public function testWorkingDirectoryIsRestored() {
    $original_dir = getcwd();
    $dir = sys_get_temp_dir() . '/fixture_test_' . uniqid();
    mkdir($dir);

    $fixture = $this->createMock(FixtureInterface::class);
    $fixture->method('id')->willReturn('test_fixture');

    $runner = new FixtureRunner([$fixture]);
    $runner->run(TRUE, $dir);

    $this->assertEquals($original_dir, getcwd());
    rmdir($dir);
  }

  public function testRunThrowsExceptionWhenWorkingDirectoryDoesNotExist() {
    $dir = '/path/to/non_existent_directory_' . uniqid();
    $fixture = $this->createMock(FixtureInterface::class);
    $runner = new FixtureRunner([$fixture]);

    $this->expectException(FixtureException::class);
    $this->expectExceptionMessage("Unable to change working directory to '$dir'.");
    $runner->run(TRUE, $dir);
  }
}
