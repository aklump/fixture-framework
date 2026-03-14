<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\RunContext;
use AKlump\FixtureFramework\RunContextStore;
use AKlump\FixtureFramework\RunContextValidator;
use AKlump\FixtureFramework\Exception\MissingRunContextKeyException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\RunContext
 * @uses \AKlump\FixtureFramework\RunContextStore
 * @uses \AKlump\FixtureFramework\RunContextValidator
 * @uses \AKlump\FixtureFramework\Exception\MissingRunContextKeyException
 * @uses \AKlump\FixtureFramework\Exception\InvalidRunContextKeyException
 */
class RunContextTest extends TestCase {

  private RunContextStore $store;
  private RunContextValidator $validator;

  protected function setUp(): void {
    $this->store = new RunContextStore();
    $this->validator = new RunContextValidator();
  }

  public function testSetDelegatesToValidatorAndStore() {
    $context = new RunContext('fixture_a', $this->store, $this->validator);
    $context->set('fixture_a.key', 'value');
    $this->assertEquals('value', $this->store->get('fixture_a.key'));
  }

  public function testGetDelegatesToStore() {
    $this->store->set('shared_key', 'shared_value');
    $context = new RunContext('fixture_a', $this->store, $this->validator);
    $this->assertEquals('shared_value', $context->get('shared_key'));
  }

  public function testHasDelegatesToStore() {
    $this->store->set('shared_key', 'shared_value');
    $context = new RunContext('fixture_a', $this->store, $this->validator);
    $this->assertTrue($context->has('shared_key'));
    $this->assertFalse($context->has('missing_key'));
  }

  public function testAllDelegatesToStore() {
    $this->store->set('k1', 'v1');
    $context = new RunContext('fixture_a', $this->store, $this->validator);
    $this->assertEquals(['k1' => 'v1'], $context->all());
  }

  public function testRequireReturnsValueWhenPresent() {
    $this->store->set('k1', 'v1');
    $context = new RunContext('fixture_a', $this->store, $this->validator);
    $this->assertEquals('v1', $context->require('k1'));
  }

  public function testRequireThrowsExceptionWhenMissing() {
    $context = new RunContext('fixture_b', $this->store, $this->validator);
    $this->expectException(MissingRunContextKeyException::class);
    $this->expectExceptionMessage('Fixture "fixture_b" requires run context key "fixture_a.user_id", but it has not been set.');
    $context->require('fixture_a.user_id');
  }

}
