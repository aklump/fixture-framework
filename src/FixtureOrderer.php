<?php

namespace AKlump\FixtureFramework;

use AKlump\FixtureFramework\Exception\FixtureException;

class FixtureOrderer {

  /**
   * @param array $fixtures List of fixture records {id, class, weight, after, before, tags}
   * @return array Ordered list of fixture records
   */
  public function order(array $fixtures): array {
    $nodes = array_keys($fixtures);
    $edges = [];

    foreach ($fixtures as $id => $record) {
      $edges[$id] = $edges[$id] ?? [];
      foreach ($record['after'] as $afterId) {
        if (!isset($fixtures[$afterId])) {
          throw new FixtureException(sprintf('Fixture "%s" depends on missing fixture "%s"', $id, $afterId));
        }
        $edges[$id][] = $afterId;
      }
      foreach ($record['before'] as $beforeId) {
        if (!isset($fixtures[$beforeId])) {
          throw new FixtureException(sprintf('Fixture "%s" must run before missing fixture "%s"', $id, $beforeId));
        }
        $edges[$beforeId] = $edges[$beforeId] ?? [];
        $edges[$beforeId][] = $id;
      }
    }

    $sorted = $this->topologicalSort($nodes, $edges, $fixtures);

    return array_map(fn($id) => $fixtures[$id], $sorted);
  }

  private function topologicalSort(array $nodes, array $edges, array $fixtures): array {
    $sorted = [];
    $visited = [];
    $visiting = [];

    // Sort nodes by weight then ID to ensure deterministic start and tie-breaking
    usort($nodes, function($a, $b) use ($fixtures) {
      if ($fixtures[$a]['weight'] !== $fixtures[$b]['weight']) {
        return $fixtures[$a]['weight'] <=> $fixtures[$b]['weight'];
      }
      return $a <=> $b;
    });

    $visit = function(string $node) use (&$visit, &$sorted, &$visited, &$visiting, $edges, $fixtures) {
      if (isset($visiting[$node])) {
        throw new FixtureException(sprintf('Circular dependency detected involving fixture "%s"', $node));
      }
      if (isset($visited[$node])) {
        return;
      }

      $visiting[$node] = true;

      $dependencies = $edges[$node] ?? [];
      // To keep it deterministic, sort dependencies too
      usort($dependencies, function($a, $b) use ($fixtures) {
        if ($fixtures[$a]['weight'] !== $fixtures[$b]['weight']) {
          return $fixtures[$a]['weight'] <=> $fixtures[$b]['weight'];
        }
        return $a <=> $b;
      });

      foreach ($dependencies as $dependency) {
        $visit($dependency);
      }

      unset($visiting[$node]);
      $visited[$node] = true;
      $sorted[] = $node;
    };

    foreach ($nodes as $node) {
      $visit($node);
    }

    return $sorted;
  }
}
