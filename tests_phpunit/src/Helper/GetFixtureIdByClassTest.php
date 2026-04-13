<?php

namespace AKlump\FixtureFramework\Tests\Helper;

use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Helper\GetFixtureIdByClass;
use AKlump\FixtureFramework\Tests\Fixtures\FixtureA;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Helper\GetFixtureIdByClass
 * @uses \AKlump\FixtureFramework\Fixture
 */
class GetFixtureIdByClassTest extends TestCase {

  public function testInvokeReturnsIdForValidFixture() {
    $helper = new GetFixtureIdByClass();
    $id = $helper(FixtureA::class);
    $this->assertEquals('fixture_a', $id);
  }

  public function testInvokeReturnsEmptyForNonInstantiableClass() {
    $helper = new GetFixtureIdByClass();
    $this->assertEquals('', $helper(FixtureInterface::class));
    $this->assertEquals('', $helper(AbstractFixtureForTest::class));
  }

  public function testInvokeReturnsEmptyForClassNotImplementingFixtureInterface() {
    $helper = new GetFixtureIdByClass();
    $this->assertEquals('', $helper(NotAFixture::class));
  }

  public function testInvokeReturnsEmptyForClassMissingFixtureAttribute() {
    $helper = new GetFixtureIdByClass();
    $this->assertEquals('', $helper(FixtureWithoutAttribute::class));
  }
}

abstract class AbstractFixtureForTest implements FixtureInterface {
  public function id(): string {
    return '';
  }
  public function __invoke(): void {
  }
  public function onSuccess(bool $silent = FALSE) {
  }
  public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = FALSE) {
  }
}

class NotAFixture {
}

class FixtureWithoutAttribute implements FixtureInterface {
  public function id(): string {
    return '';
  }
  public function __invoke(): void {
  }
  public function onSuccess(bool $silent = FALSE) {
  }
  public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = FALSE) {
  }
}
