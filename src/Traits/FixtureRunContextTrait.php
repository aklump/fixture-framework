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

  /**
   * @var \AKlump\FixtureFramework\Runtime\RunContext
   *
   * @deprecated Use context() instead, this property will be made private in a
   * * future version.
 */
  public RunContext $runContext;

  public function setRunContext(RunContext $run_context): void {
    $this->runContext = $run_context;
  }

  public function context(): RunContext {
    return $this->runContext;
  }

}
