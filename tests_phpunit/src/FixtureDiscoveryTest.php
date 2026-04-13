<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\Discovery\FixtureDiscovery;
use AKlump\FixtureFramework\Exception\FixtureException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Discovery\FixtureDiscovery
 * @uses \AKlump\FixtureFramework\Fixture
 */
class FixtureDiscoveryTest extends TestCase {

  public function testDiscoverIgnoresPerDiscoverable() {
    $discovery = new FixtureDiscovery(__DIR__ . '/../../vendor');
    // We must restrict discovery to a namespace that doesn't have duplicates
    // because DuplicateFixture is now in the autoload path.
    $fixtures = $discovery->discover(['AKlump\FixtureFramework\Tests\Fixtures\\']);

    $this->assertArrayHasKey('fixture_a', $fixtures);
    $this->assertArrayHasKey('fixture_b', $fixtures);
  }

  public function testDiscoverWithAllowList() {
    $discovery = new FixtureDiscovery(__DIR__ . '/../../vendor');

    // Should include when namespace matches
    $fixtures = $discovery->discover(['AKlump\FixtureFramework\Tests\Fixtures\\']);
    $this->assertArrayHasKey('fixture_a', $fixtures);
    $this->assertArrayHasKey('fixture_b', $fixtures);

    // Should exclude when namespace doesn't match
    $fixtures = $discovery->discover(['NonExistent\Namespace\\']);
    $this->assertArrayNotHasKey('fixture_a', $fixtures);
    $this->assertArrayNotHasKey('fixture_b', $fixtures);

    // Multiple namespaces
    $fixtures = $discovery->discover(['NonExistent\Namespace\\', 'AKlump\FixtureFramework\Tests\Fixtures\\']);
    $this->assertArrayHasKey('fixture_a', $fixtures);
    $this->assertArrayHasKey('fixture_b', $fixtures);
  }

  public function testDuplicateFixtureIdThrowsException() {
    $this->expectException(FixtureException::class);
    $this->expectExceptionMessage('Duplicate fixture id "fixture_a" found');
    $discovery = new FixtureDiscovery(__DIR__ . '/../../vendor');
    // We include both namespaces to trigger the duplicate ID error
    $discovery->discover([
      'AKlump\FixtureFramework\Tests\Fixtures\\',
      'AKlump\FixtureFramework\Tests\FixturesDuplicate\\',
    ]);
  }

  public function testEmptyFixtureIdThrowsException() {
    $this->expectException(FixtureException::class);
    $this->expectExceptionMessage('Fixture id must be a non-empty string');
    $discovery = new FixtureDiscovery(__DIR__ . '/../../vendor');
    $discovery->discover(['AKlump\FixtureFramework\Tests\FixturesEmptyId\\']);
  }

  public function testNormalizeStringListWithNonStringThrowsException() {
    $this->expectException(FixtureException::class);
    $this->expectExceptionMessage('has non-string value');
    $discovery = new FixtureDiscovery(__DIR__ . '/../../vendor');
    $discovery->discover(['AKlump\FixtureFramework\Tests\FixturesNonStringValue\\']);
  }

  public function testDiscoverSkipsInvalidClasses() {
    $discovery = new FixtureDiscovery(__DIR__ . '/../../vendor');
    $fixtures = $discovery->discover(['AKlump\FixtureFramework\Tests\FixturesInvalid\\']);
    $this->assertEmpty($fixtures);
  }

  public function testNormalizeStringListWithDedupe() {
    $discovery = new FixtureDiscovery(__DIR__ . '/../../vendor');
    $fixtures = $discovery->discover(['AKlump\FixtureFramework\Tests\FixturesDedupe\\']);
    $this->assertArrayHasKey('dedupe', $fixtures);
    $this->assertEquals(['tag1', 'tag2'], $fixtures['dedupe']['tags']);
  }

  public function testDiscoveryOutputsNamespacesWhenNotSilent() {
    $discovery = new FixtureDiscovery(__DIR__ . '/../../vendor');
    $discovery->setSilent(false);
    $this->expectOutputRegex('/Scanning namespaces: /');
    $discovery->discover(['AKlump\FixtureFramework\Tests\Fixtures\\']);
  }

  public function testDiscoveryOutputsNothingWhenSilent() {
    $discovery = new FixtureDiscovery(__DIR__ . '/../../vendor');
    $discovery->setSilent(true);
    $this->expectOutputString('');
    $discovery->discover(['AKlump\FixtureFramework\Tests\Fixtures\\']);
  }
}
