<?php

namespace AKlump\TestFixture\Tests\Fixtures;

use AKlump\TestFixture\AbstractFixture;

use AKlump\TestFixture\RunOptions;

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
