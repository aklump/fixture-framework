<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\Exception\InvalidRunOptionsException;
use AKlump\FixtureFramework\Exception\MissingRunOptionException;
use AKlump\FixtureFramework\RunOptions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\RunOptions
 * @uses \AKlump\FixtureFramework\RunOptionsValidator
 * @uses \AKlump\FixtureFramework\Exception\MissingRunOptionException
 * @uses \AKlump\FixtureFramework\Exception\InvalidRunOptionsException
 */
class RunOptionsTest extends TestCase {

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
}
