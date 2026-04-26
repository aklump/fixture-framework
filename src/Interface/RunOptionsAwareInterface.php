<?php

namespace AKlump\FixtureFramework\Interface;

use AKlump\FixtureFramework\Runtime\RunOptions;

/**
 * Represents fixtures that receive global run options.
 */
interface RunOptionsAwareInterface {

  /**
   * Sets the global run options.
   *
   * @param \AKlump\FixtureFramework\Runtime\RunOptions $options
   *   The run options.
   *
   * @return void
   */
  public function setRunOptions(RunOptions $options): void;

  /**
   * Returns the global run options.
   *
   * @return \AKlump\FixtureFramework\Runtime\RunOptions
   *   The run options.
   */
  public function options(): RunOptions;

}
