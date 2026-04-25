#!/usr/bin/env php
<?php
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
  $options = [
      'env' => 'test',
      'url' => 'https://website.com/',
      'drush' => 'lando nxdb_drush',
  ];
  $validator = new \AKlump\FixtureFramework\Runtime\RunContextValidator();
  $fixtures = (new \AKlump\FixtureFramework\Runtime\FixtureCollectionBuilder($options, $validator))($definitions);
  $runner = new \AKlump\FixtureFramework\Runtime\FixtureRunner($fixtures);
  $runner->run($silent);
}
catch (\Exception $e) {
  print $e->getMessage() . "\n";
  exit(1);
}
