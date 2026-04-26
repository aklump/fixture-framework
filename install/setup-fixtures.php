#!/usr/bin/env php
<?php

use AKlump\FixtureFramework\Runtime\RunOptions;

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
  $run_options_validator = new \AKlump\FixtureFramework\Runtime\RunOptionsValidator();
  $run_options = new RunOptions([
      'env' => 'test',
      'url' => 'https://website.com/',
      'drush' => 'lando nxdb_drush',
      $run_options_validator,
  ]);

  $run_context_validator = new \AKlump\FixtureFramework\Runtime\RunContextValidator();
  $instantiator = new \AKlump\FixtureFramework\Runtime\FixtureInstantiator($run_options, $run_context_validator);

  $fixtures = (new \AKlump\FixtureFramework\Runtime\FixtureCollectionBuilder($instantiator))($definitions);

  $runner = new \AKlump\FixtureFramework\Runtime\FixtureRunner($fixtures);
  $runner->run($silent);
}
catch (\Exception $e) {
  print $e->getMessage() . "\n";
  exit(1);
}
