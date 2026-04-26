<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Exception\MissingRunContextKeyException;

class RunContext {

  private string $fixtureId;

  private RunContextStore $store;

  private RunContextValidator $validator;

  public function __construct(
    string $fixtureId,
    RunContextStore $store,
    ?RunContextValidator $validator = NULL,
  ) {
    $this->fixtureId = $fixtureId;
    $this->store = $store;
    $this->validator = $validator ?? new RunContextValidator();
  }

  public function set(string $key, mixed $value): void {
    $this->validator->validateSet($this->fixtureId, $key, $value);
    $this->store->set($key, $value);
  }

  public function get(string $key, mixed $default = NULL): mixed {
    return $this->store->get($key, $default);
  }

  public function has(string $key): bool {
    return $this->store->has($key);
  }

  public function require(string $key): mixed {
    if (!$this->has($key)) {
      throw new MissingRunContextKeyException($this->fixtureId, $key);
    }

    return $this->get($key);
  }

  public function all(): array {
    return $this->store->all();
  }

}
