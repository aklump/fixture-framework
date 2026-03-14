<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\RunContextValidator;
use AKlump\FixtureFramework\Exception\InvalidRunContextKeyException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\RunContextValidator
 * @uses \AKlump\FixtureFramework\RunContextValidator
 * @uses \AKlump\FixtureFramework\Exception\InvalidRunContextKeyException
 */
class RunContextValidatorTest extends TestCase {

  public function testValidateSetPassesWithCorrectPrefix() {
    $validator = new RunContextValidator();
    $validator->validateSet('fixture_a', 'fixture_a.key', 'value');
    $this->addToAssertionCount(1);
  }

  public function testValidateSetThrowsWithIncorrectPrefix() {
    $validator = new RunContextValidator();
    $this->expectException(InvalidRunContextKeyException::class);
    $this->expectExceptionMessage('Run context key "key" must begin with "fixture_a.".');
    $validator->validateSet('fixture_a', 'key', 'value');
  }

  public function testValidateSetThrowsWithOtherFixturePrefix() {
    $validator = new RunContextValidator();
    $this->expectException(InvalidRunContextKeyException::class);
    $this->expectExceptionMessage('Run context key "fixture_b.key" must begin with "fixture_a.".');
    $validator->validateSet('fixture_a', 'fixture_b.key', 'value');
  }

  public function testValidateSetThrowsWithPartialPrefix() {
    $validator = new RunContextValidator();
    $this->expectException(InvalidRunContextKeyException::class);
    // Prefix should be 'fixture_a.' not just 'fixture_a'
    $this->expectExceptionMessage('Run context key "fixture_akey" must begin with "fixture_a.".');
    $validator->validateSet('fixture_a', 'fixture_akey', 'value');
  }

}
