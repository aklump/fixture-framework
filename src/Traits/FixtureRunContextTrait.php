<?php

namespace AKlump\FixtureFramework\Traits;

use AKlump\FixtureFramework\Runtime\RunContext;

/**
 * Provides access to shared runtime values for the current fixture run.
 *
 * This property is populated by FixtureRunner.
 * It is separate from metadata and is isolated to a single runner execution.
 */
trait FixtureRunContextTrait {

  public RunContext $runContext;

}
