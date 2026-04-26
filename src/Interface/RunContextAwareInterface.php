<?php

namespace AKlump\FixtureFramework\Interface;

use AKlump\FixtureFramework\Runtime\RunContext;

/**
 * Represents fixtures that receive a per-fixture run context.
 */
interface RunContextAwareInterface {

  public function setRunContext(RunContext $run_context): void;

  public function context(): RunContext;

}
