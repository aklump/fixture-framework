<?php

namespace AKlump\FixtureFramework;

use AKlump\FixtureFramework\Exception\FixtureException;

interface FixtureInterface {

  /**
   * Returns the unique identifier for the fixture.
   *
   * @return string
   *   The fixture ID.
   */
  public function id(): string;

  /**
   * Returns a brief description of what the fixture does.
   *
   * The description should be a short, human-readable summary suitable for
   * compact display in CLI tables, catalogs, or reports (e.g., a concise phrase
   * or sentence). Longer developer-facing documentation, implementation
   * details, or warnings should remain in the class docblock.
   *
   * @return string
   *   The fixture description.
   */
  public function description(): string;

  /**
   * Executes the fixture logic.
   *
   * This is where the actual work of the fixture (e.g., seeding data)
   * should be implemented.
   *
   * @return void
   *
   * @throws \AKlump\FixtureFramework\Exception\FixtureException
   *   If the fixture fails to execute correctly.
   */
  public function __invoke(): void;

  /**
   * Callback executed when the fixture completes successfully.
   *
   * @param bool $silent
   *   If TRUE, the callback should not produce any output.
   *
   * @return mixed
   */
  public function onSuccess(bool $silent = FALSE);

  /**
   * Callback executed when the fixture throws a FixtureException.
   *
   * @param \AKlump\FixtureFramework\Exception\FixtureException $e
   *   The exception that was thrown.
   * @param bool $silent
   *   If TRUE, the callback should not produce any output.
   *
   * @return mixed
   */
  public function onFailure(FixtureException $e, bool $silent = FALSE);
}
