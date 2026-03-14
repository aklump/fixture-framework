<?php

namespace AKlump\TestFixture\Tests\Fixtures;

use AKlump\TestFixture\AbstractFixture;

class OptionsTestFixture extends AbstractFixture {
  public static array $receivedOptionsInSetUp = [];
  public static array $receivedOptionsInOnSuccess = [];
  
  public function setUp(): void {
    self::$receivedOptionsInSetUp = $this->options;
  }
  
  public function onSuccess(bool $silent = FALSE) {
    self::$receivedOptionsInOnSuccess = $this->options;
    parent::onSuccess($silent);
  }
}
