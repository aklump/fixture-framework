<?php

namespace AKlump\TestFixture;

use AKlump\TestFixture\Exception\InvalidRunOptionsException;

/**
 * Validates run options recursively to ensure only plain data is used.
 */
class RunOptionsValidator {

  /**
   * Validate options array recursively.
   *
   * @param array $options
   *   The options to validate.
   * @param string $path
   *   The current traversal path.
   *
   * @throws \AKlump\TestFixture\Exception\InvalidRunOptionsException
   */
  public function validate(array $options, string $path = ''): void {
    foreach ($options as $key => $value) {
      $current_path = $path === '' ? (string) $key : "$path.$key";
      if (is_array($value)) {
        $this->validate($value, $current_path);
      }
      elseif (NULL !== $value && !is_scalar($value)) {
        throw InvalidRunOptionsException::forPath($current_path);
      }
    }
  }

}
