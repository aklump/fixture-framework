<?php

namespace AKlump\FixtureFramework\Tests\Fixtures;

use AKlump\FixtureFramework\AbstractFixture;

use AKlump\FixtureFramework\RunOptions;

class OptionsTestFixture extends AbstractFixture {
  public static ?RunOptions $receivedOptionsInSetUp = NULL;
  public static ?RunOptions $receivedOptionsInOnSuccess = NULL;
  
  public function setUp(): void {
    self::$receivedOptionsInSetUp = $this->options;
  }
  
  public function onSuccess(bool $silent = FALSE) {
    self::$receivedOptionsInOnSuccess = $this->options;
    parent::onSuccess($silent);
  }
}
