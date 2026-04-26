<?php

namespace AKlump\FixtureFramework\Interface;

/**
 * Represents fixtures that receive their normalized fixture definition.
 */
interface FixtureDefinitionAwareInterface {

  /**
   * Sets the normalized fixture definition.
   *
   * @param array $definition
   *   The fixture definition as defined in the #[Fixture] attribute.
   *
   * @return void
   */
  public function setFixtureDefinition(array $definition): void;

  /**
   * Returns the normalized fixture definition.
   *
   * @return array
   *   The fixture definition.
   */
  public function fixture(): array;

}
