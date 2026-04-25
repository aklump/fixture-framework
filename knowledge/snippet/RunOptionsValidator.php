<?php

namespace AKlump\Directio\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Exception\InvalidRunOptionsException;
use Symfony\Component\Console\Output\OutputInterface;

class RunOptionsValidator extends \AKlump\FixtureFramework\Runtime\RunOptionsValidator {

  public function validate(array $options, string $path = ''): void {
    if (isset($options['output'])) {
      if ($options['output'] instanceof OutputInterface) {
        return;
      }
      throw new InvalidRunOptionsException("Invalid output option, it must be an instance of " . OutputInterface::class);
    }

    parent::validate($options, $path);
  }

}
