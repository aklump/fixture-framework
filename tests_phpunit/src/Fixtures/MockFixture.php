<?php

namespace AKlump\TestFixture\Tests\Fixtures;

use AKlump\TestFixture\AbstractFixture;
use AKlump\TestFixture\Exception\FixtureException;

class MockFixture extends AbstractFixture {
  public static int $successCount = 0;
  public static int $failureCount = 0;
  public static bool $shouldFail = false;

  public function setUp(): void {
    if (self::$shouldFail) {
      throw new FixtureException('Fixture failed');
    }
  }

  public function onSuccess(bool $silent = FALSE) {
    self::$successCount++;
  }

  public function onFailure(FixtureException $e, bool $silent = FALSE) {
    self::$failureCount++;
    throw $e;
  }
}
