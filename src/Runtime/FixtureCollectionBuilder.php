<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Helper\FixtureInstantiator;

class FixtureCollectionBuilder {

  public function __construct(
    private array|RunOptions $options,
    private RunContextValidator $validator
  ) {
  }

  public function __invoke(array $definitions) {
    $store = new RunContextStore();

    return array_map(fn(array $definition) => (new FixtureInstantiator())(
      $definition,
      $this->options,
      $store,
      $this->validator
    ), $definitions);
  }
}
