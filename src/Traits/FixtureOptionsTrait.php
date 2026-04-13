<?php

namespace AKlump\FixtureFramework\Traits;

use AKlump\FixtureFramework\Runtime\RunOptions;

/**
 * Trait to provide a global runtime options property to a fixture.
 *
 * This trait adds a public $options property which is automatically populated by
 * the FixtureRunner before the fixture's __invoke() method is called.
 *
 * - This property contains the global runtime options passed into FixtureRunner.
 * - It is populated before __invoke() is called.
 * - It remains available during __invoke(), onSuccess(), and onFailure().
 * - It is separate from metadata and run context.
 */
trait FixtureOptionsTrait {

  /**
   * Global runtime options passed into FixtureRunner.
   *
   * @var \AKlump\FixtureFramework\RunOptions
   */
  public RunOptions $options;

}
