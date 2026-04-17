<?php

namespace AKlump\Knowledge\User;

class GetReadMeCode {

  use CodeExtractionTrait;

  public function __invoke(string $book_path) {
    $file = dirname($book_path) . '/install/setup-fixtures.php';
    if (!file_exists($file)) {
      throw new \InvalidArgumentException("Could not find $file");
    }
    $content = file_get_contents($file);

    return $this->extractReadMeSnippets($content)['setup-fixtures'] ?? '';
  }

}
