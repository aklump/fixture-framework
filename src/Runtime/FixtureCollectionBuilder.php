<?php

namespace AKlump\FixtureFramework\Runtime;

class FixtureCollectionBuilder {

  public function __construct(
    private readonly FixtureInstantiator $instantiator,
  ) {
  }

  public function __invoke(array $definitions) {
    $store = new RunContextStore();

    return array_map(fn(array $definition) => ($this->instantiator)(
      $definition,
      $store,
    ), $definitions);
  }
}
