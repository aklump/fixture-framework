<?php

namespace AKlump\FixtureFramework;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Fixture {

  public function __construct(
    public string $id,
    public string $description = "",
    public int $weight = 0,
    public array $after = [],
    public array $before = [],
    public array $tags = [],
    public bool $discoverable = true,
  ) {
  }
}
