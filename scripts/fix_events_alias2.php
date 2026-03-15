<?php

/**
 * Delete the /events path alias entity directly.
 */

$storage = \Drupal::entityTypeManager()->getStorage('path_alias');

$aliases = $storage->loadByProperties(['alias' => '/events']);
if (empty($aliases)) {
  echo "No path_alias entity with alias /events found.\n";
} else {
  foreach ($aliases as $alias_entity) {
    echo "Deleting alias entity id={$alias_entity->id()} path={$alias_entity->getPath()} alias={$alias_entity->getAlias()}\n";
    $alias_entity->delete();
  }
  echo "✓ Done.\n";
}

// Also flush path alias cache.
\Drupal::service('path_alias.manager')->cacheClear();
echo "Cache cleared.\n";
