<?php

namespace AKlump\FixtureFramework\Helper;

use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Fixture;

class GetFixtureIdByClass {

  /**
   * Invokes the class and retrieves the ID of the Fixture attribute, if available.
   *
   * @param string $classname The fully qualified name of the class to reflect upon.
   *
   * @return string The ID of the Fixture attribute if the class is instantiable
   * and implements the FixtureInterface, or an empty string otherwise.
   * @throws \ReflectionException
   */
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
