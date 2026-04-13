<?php

namespace AKlump\FixtureFramework;

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
   */
  public array $fixture;

}
