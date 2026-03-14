<?php

namespace AKlump\TestFixture\Tests;

use AKlump\TestFixture\Exception\InvalidRunOptionsException;
use AKlump\TestFixture\Exception\MissingRunOptionException;
use AKlump\TestFixture\RunOptions;
use PHPUnit\Framework\TestCase;

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
