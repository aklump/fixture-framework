<?php

namespace AKlump\TestFixture;

/**
 * Trait to provide a global runtime options property to a fixture.
 *
 * This trait adds a public $options property which is automatically populated by
 * the FixtureRunner before the fixture's setUp() method is called.
 *
 * - This property contains the global runtime options passed into FixtureRunner.
 * - It is populated before setUp() is called.
 * - It remains available during setUp(), onSuccess(), and onFailure().
 * - It is separate from metadata and run context.
 */
trait FixtureOptionsTrait {

  /**
   * Global runtime options passed into FixtureRunner.
   *
   * @var array
   */
  public array $options;

}
