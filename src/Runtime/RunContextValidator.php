<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Exception\InvalidRunContextKeyException;

class RunContextValidator {

  public function validateSet(string $fixtureId, string $key, mixed $value): void {
    if (!str_starts_with($key, $fixtureId . '.')) {
      throw new InvalidRunContextKeyException($fixtureId, $key);
    }
  }

}
