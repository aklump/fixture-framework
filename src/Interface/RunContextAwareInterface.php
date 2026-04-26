<?php

namespace AKlump\FixtureFramework\Interface;

use AKlump\FixtureFramework\Runtime\RunContext;

/**
 * Represents fixtures that receive a per-fixture run context.
 */
interface RunContextAwareInterface {

  /**
   * Sets the per-fixture run context.
   *
   * @param \AKlump\FixtureFramework\Runtime\RunContext $run_context
   *   The run context.
   *
   * @return void
   */
  public function setRunContext(RunContext $run_context): void;

  /**
   * Returns the per-fixture run context.
   *
   * @return \AKlump\FixtureFramework\Runtime\RunContext
   *   The run context.
   */
  public function context(): RunContext;

}
