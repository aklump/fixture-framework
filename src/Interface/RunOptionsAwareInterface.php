<?php

namespace AKlump\FixtureFramework\Interface;

use AKlump\FixtureFramework\Runtime\RunOptions;

/**
 * Represents fixtures that receive global run options.
 */
interface RunOptionsAwareInterface {

  public function setRunOptions(RunOptions $options): void;

  public function options(): RunOptions;

}
