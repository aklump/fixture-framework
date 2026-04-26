<?php

namespace AKlump\FixtureFramework\Tests\Runtime;

use AKlump\FixtureFramework\Exception\InvalidRunOptionsException;
use AKlump\FixtureFramework\Exception\MissingRunOptionException;
use AKlump\FixtureFramework\Runtime\RunOptions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Runtime\RunOptions
 * @uses \AKlump\FixtureFramework\Runtime\RunOptionsValidator
 * @uses \AKlump\FixtureFramework\Exception\MissingRunOptionException
 * @uses \AKlump\FixtureFramework\Exception\InvalidRunOptionsException
 */
class RunOptionsTest extends TestCase {

  public function testEmptyConstructor() {
    $options = new RunOptions();
    $this->assertEmpty($options->all());
  }

  public function testGet() {
    $options = new RunOptions(['env' => 'test']);
    $this->assertEquals('test', $options->get('env'));
    $this->assertNull($options->get('missing'));
    $this->assertEquals('default', $options->get('missing', 'default'));
  }

  public function testHas() {
    $options = new RunOptions(['env' => 'test', 'null_val' => null]);
    $this->assertTrue($options->has('env'));
    $this->assertTrue($options->has('null_val'));
    $this->assertFalse($options->has('missing'));
  }

  public function testRequire() {
    $options = new RunOptions(['env' => 'test']);
    $this->assertEquals('test', $options->require('env'));
  }

  public function testRequireThrowsExceptionWhenMissing() {
    $options = new RunOptions(['env' => 'test']);
    $this->expectException(MissingRunOptionException::class);
    $this->expectExceptionMessage('Required run option "missing" is missing.');
    $options->require('missing');
  }

  public function testAll() {
    $data = ['env' => 'test', 'debug' => true];
    $options = new RunOptions($data);
    $this->assertEquals($data, $options->all());
  }

  public function testValidationOnConstruction() {
    $this->expectException(InvalidRunOptionsException::class);
    new RunOptions(['client' => new \stdClass()]);
  }

  public function testFromArray() {
    $options = RunOptions::fromArray(['env' => 'prod']);
    $this->assertInstanceOf(RunOptions::class, $options);
    $this->assertEquals('prod', $options->get('env'));
  }

  public function testWithAddedOptions() {
    $options = new RunOptions(['env' => 'test']);
    $newOptions = $options->withAddedOptions(['debug' => true]);

    $this->assertNotSame($options, $newOptions);
    $this->assertEquals('test', $newOptions->get('env'));
    $this->assertTrue($newOptions->get('debug'));
    $this->assertEquals(['env' => 'test', 'debug' => true], $newOptions->all());
  }

  public function testWithAddedOptionsThrowsOnConflict() {
    $options = new RunOptions(['env' => 'test']);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The following keys may not be overridden: env');
    $options->withAddedOptions(['env' => 'prod']);
  }

  public function testWithAddedOptionsValidatesNewOptions() {
    $options = new RunOptions(['env' => 'test']);
    $this->expectException(InvalidRunOptionsException::class);
    $options->withAddedOptions(['client' => new \stdClass()]);
  }
}
