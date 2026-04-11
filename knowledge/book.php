<?php

/** @var string $command */
/** @var string $book_path */
/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */

$dispatcher->addListener(\AKlump\Knowledge\Events\GetVariables::NAME, function (\AKlump\Knowledge\Events\GetVariables $event) {
  $book_path = $event->getPathToSource();
  $event->setVariable('setup-fixtures', (new \AKlump\Knowledge\User\GetReadMeCode())($book_path));
});
