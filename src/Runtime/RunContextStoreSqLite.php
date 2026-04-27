<?php

namespace AKlump\FixtureFramework\Runtime;

use AKlump\FixtureFramework\Exception\RunContextKeyAlreadyExistsException;
use PDO;

class RunContextStoreSqLite implements RunContextStoreInterface {

  private PDO $pdo;

  private string $tableName;

  /**
   * @var array
   */
  private array $cache = [];

  /**
   * @var bool
   */
  private bool $isCacheComplete = false;

  /**
   * @param string $db_path The path to the SQLite database file.
   *   E.g., '/path/to/database.sqlite'
   * @param string $table_name The name of the table to use for storage.
   */
  public function __construct(string $db_path, string $table_name = 'run_context') {
    $this->tableName = $table_name;
    $this->pdo = new PDO("sqlite:$db_path");
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->exec("CREATE TABLE IF NOT EXISTS \"$this->tableName\" (\"key\" TEXT PRIMARY KEY, \"value\" BLOB)");
  }

  /**
   * {@inheritdoc}
   */
  public function set(string $key, mixed $value): void {
    if ($this->has($key)) {
      throw new RunContextKeyAlreadyExistsException($key);
    }
    $statement = $this->pdo->prepare("INSERT INTO \"$this->tableName\" (\"key\", \"value\") VALUES (:key, :value)");
    $statement->execute([
      ':key' => $key,
      ':value' => serialize($value),
    ]);
    $this->cache[$key] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $this->cache)) {
      return $this->cache[$key];
    }
    $statement = $this->pdo->prepare("SELECT \"value\" FROM \"$this->tableName\" WHERE \"key\" = :key");
    $statement->execute([':key' => $key]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $value = unserialize($row['value']);
      $this->cache[$key] = $value;

      return $value;
    }

    return $default;
  }

  /**
   * {@inheritdoc}
   */
  public function has(string $key): bool {
    if (array_key_exists($key, $this->cache)) {
      return true;
    }
    $statement = $this->pdo->prepare("SELECT \"value\" FROM \"$this->tableName\" WHERE \"key\" = :key");
    $statement->execute([':key' => $key]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $this->cache[$key] = unserialize($row['value']);

      return true;
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function all(): array {
    if ($this->isCacheComplete) {
      return $this->cache;
    }
    $statement = $this->pdo->query("SELECT \"key\", \"value\" FROM \"$this->tableName\"");
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
      $this->cache[$row['key']] = unserialize($row['value']);
    }
    $this->isCacheComplete = true;

    return $this->cache;
  }

}
