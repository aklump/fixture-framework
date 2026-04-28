#!/usr/bin/env php
<?php

use AKlump\FixtureFramework\Runtime\FixtureCollectionBuilder;
use AKlump\FixtureFramework\Runtime\FixtureInstantiator;
use AKlump\FixtureFramework\Runtime\FixtureRunner;
use AKlump\FixtureFramework\Runtime\RunContextStore;
use AKlump\FixtureFramework\Runtime\RunContextValidator;
use AKlump\FixtureFramework\Runtime\RunOptions;
use AKlump\FixtureFramework\Runtime\RunOptionsValidator;

$vendor_dir = __DIR__ . '/../vendor';
require_once $vendor_dir . '/autoload.php';

$flush = in_array('--flush', $argv);
$silent = in_array('--silent', $argv);
$filter = '';
foreach ($argv as $arg) {
  if (str_starts_with($arg, '--filter=')) {
    $filter = substr($arg, 9);
    break;
  }
}

try {
  $definitions = (new \AKlump\FixtureFramework\Discovery\DiscoverFixtureDefinitions())($vendor_dir, [
      'MyApp\Tests\Fixture',
  ], $flush, $silent, $filter);
}
catch (Exception $e) {
  echo "Error ordering fixtures: " . $e->getMessage() . "\n";
  exit(1);
}

try {
  /**
   * @var \AKlump\FixtureFramework\Runtime\RunOptionsValidator
   * $run_options_validator This class can be overridden to provide custom
   * runtime options validation.
   */
  $run_options_validator = new RunOptionsValidator();

  /**
   * @var \AKlump\FixtureFramework\Runtime\RunOptions The immutable runtime
   * options provided to all fixtures.
   */
  $run_options = new RunOptions([
      'env' => 'test',
      'url' => 'https://website.com/',
      'drush' => 'lando nxdb_drush',
  ], $run_options_validator);

  /**
   * @var \AKlump\FixtureFramework\Runtime\FixtureInstantiator This class can be
   * overridden to provide custom runtime context validation.
   */
  $run_context_validator = new RunContextValidator();

  /**
   * @var \AKlump\FixtureFramework\Runtime\FixtureInstantiator This class can be
   * overridden to provide custom fixture instantiation if needed.
   */
  $instantiator = new FixtureInstantiator($run_options, $run_context_validator);

  /**
   * @var \AKlump\FixtureFramework\Runtime\RunContextStoreInterface This class
   * handles the storage of the runtime context.
   */
  $store = new RunContextStore();

  /**
   * @var \AKlump\FixtureFramework\FixtureInterface[] $fixtures The list of
   * fixtures to run as objects.
   */
  $fixtures = (new FixtureCollectionBuilder($instantiator))($definitions, $store);

  /**
   * @var \AKlump\FixtureFramework\Runtime\FixtureRunner The class in charge of
   * running the fixtures.
   */
  $runner = new FixtureRunner($fixtures);
  $runner->run($silent);
}
catch (\Exception $e) {
  print $e->getMessage() . "\n";
  exit(1);
}
