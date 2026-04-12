<?php

namespace AKlump\FixtureFramework\Helper;

use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Fixture;

class GetFixtureIdByClassname {

  public function __invoke(string $classname): string {
    $reflection = new \ReflectionClass($classname);
    if (!$reflection->isInstantiable()
      || !$reflection->implementsInterface(FixtureInterface::class)) {
      return '';
    }
    $attribute = $reflection->getAttributes(Fixture::class)[0] ?? NULL;

    return $attribute?->newInstance()->id ?? '';
  }
}
