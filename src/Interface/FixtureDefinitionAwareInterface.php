<?php

namespace AKlump\FixtureFramework\Interface;

/**
 * Represents fixtures that receive their normalized fixture definition.
 */
interface FixtureDefinitionAwareInterface {

  public function setFixtureDefinition(array $definition): void;

  public function fixture(): array;

}
