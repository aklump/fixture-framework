<?php

namespace AKlump\FixtureFramework\Interface;

interface InitializableFixtureInterface {

  /**
   * Called once after the fixture has been instantiated and runtime options
   * have been assigned.
   */
  public function initialize(): void;

}
