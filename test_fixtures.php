#!/usr/bin/env php
<?php
// <readme id="run_fixtures_php">
$vendor_dir = __DIR__ . '/../vendor';
require_once $vendor_dir . '/autoload.php';

$flush = in_array('--flush', $argv);
$silent = in_array('--silent', $argv);

try {
  $fixtures = (new \AKlump\TestFixture\Helper\GetFixtures())($vendor_dir, [
      'MyApp\Tests\Fixture',
  ], $flush, $silent);
}
catch (Exception $e) {
  echo "Error ordering fixtures: " . $e->getMessage() . "\n";
  exit(1);
}

try {
  $options = ['env' => 'test', 'drush' => 'lando nxdb_drush'];
  $runner = new \AKlump\TestFixture\FixtureRunner($fixtures, $options);
  $runner->run($silent);
}
catch (\Exception $e) {
  exit(1);
}
// </readme>
