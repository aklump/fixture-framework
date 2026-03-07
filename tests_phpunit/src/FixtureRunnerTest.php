<?php

namespace AKlump\TestFixture\Tests;

use AKlump\TestFixture\Exception\FixtureException;
use AKlump\TestFixture\FixtureRunner;
use AKlump\TestFixture\Tests\Fixtures\FixtureA;
use AKlump\TestFixture\Tests\Fixtures\FixtureB;
use AKlump\TestFixture\Tests\Fixtures\FixtureWithData;
use AKlump\TestFixture\Tests\Fixtures\FixtureWithTrait;
use AKlump\TestFixture\Tests\Fixtures\MockFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\TestFixture\FixtureRunner
 * @uses \AKlump\TestFixture\AbstractFixture
 */
class FixtureRunnerTest extends TestCase {

  public function testOnSuccessAndOnFailure() {
    MockFixture::$successCount = 0;
    MockFixture::$failureCount = 0;

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
    $this->expectOutputString(sprintf('Executing fixture "fixture_a" (%s)... %sDone.%s', FixtureA::class, PHP_EOL, PHP_EOL));
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
    $this->expectOutputString("No fixtures found for execution. Check your classes for the #[AKlump\TestFixture\Fixture] attribute." . PHP_EOL);
    $runner->run(FALSE);
  }

  public function testRunEmptyFixturesSilentOutputsNothing() {
    $runner = new FixtureRunner([], []);
    $this->expectOutputString("");
    $runner->run(TRUE);
  }
}
