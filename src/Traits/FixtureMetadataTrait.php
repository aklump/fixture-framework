<?php

namespace AKlump\FixtureFramework\Traits;

/**
 * Trait to provide a common property for accessing fixture metadata.
 *
 * Use this trait in your Fixture classes to have the metadata automatically
 * populated by the FixtureRunner before the __invoke() method is called.
 */
trait FixtureMetadataTrait {

  /**
   * The fixture metadata record as discovered by FixtureDiscovery.
   *
   * @var array
   *
   * @deprecated Use fixture() instead, this property will be made private in a
   * future version.
   */
  public array $fixture;

  public function setFixtureDefinition(array $definition): void {
    $this->fixture = $definition;
  }

  public function fixture(): array {
    return $this->fixture;
  }

}
