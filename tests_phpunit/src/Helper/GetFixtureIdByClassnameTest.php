<?php

namespace AKlump\FixtureFramework\Tests\Helper;

use AKlump\FixtureFramework\Fixture;
use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Helper\GetFixtureIdByClassname;
use AKlump\FixtureFramework\Tests\Fixtures\FixtureA;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Helper\GetFixtureIdByClassname
 * @uses \AKlump\FixtureFramework\Fixture
 */
class GetFixtureIdByClassnameTest extends TestCase {

  public function testInvokeReturnsIdForValidFixture() {
    $helper = new GetFixtureIdByClassname();
    $id = $helper(FixtureA::class);
    $this->assertEquals('fixture_a', $id);
  }

  public function testInvokeReturnsEmptyForNonInstantiableClass() {
    $helper = new GetFixtureIdByClassname();
    $this->assertEquals('', $helper(FixtureInterface::class));
    $this->assertEquals('', $helper(AbstractFixtureForTest::class));
  }

  public function testInvokeReturnsEmptyForClassNotImplementingFixtureInterface() {
    $helper = new GetFixtureIdByClassname();
    $this->assertEquals('', $helper(NotAFixture::class));
  }

  public function testInvokeReturnsEmptyForClassMissingFixtureAttribute() {
    $helper = new GetFixtureIdByClassname();
    $this->assertEquals('', $helper(FixtureWithoutAttribute::class));
  }
}

abstract class AbstractFixtureForTest implements FixtureInterface {
}

class NotAFixture {
}

class FixtureWithoutAttribute implements FixtureInterface {
  public function setUp(): void {
  }
  public function onSuccess(bool $silent = FALSE) {
  }
  public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = FALSE) {
  }
}
