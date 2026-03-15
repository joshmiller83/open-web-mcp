<?php

/**
 * Remove the /events path alias from node/7 so the Views route takes over.
 */

$db = \Drupal::database();

// Find alias records pointing to /events.
$rows = $db->select('path_alias', 'pa')
  ->fields('pa', ['id', 'path', 'alias', 'langcode'])
  ->condition('alias', '/events')
  ->execute()
  ->fetchAll();

if (empty($rows)) {
  echo "No /events alias found.\n";
} else {
  foreach ($rows as $row) {
    echo "Found alias id={$row->id} path={$row->path} alias={$row->alias}\n";
  }
}

// Load node 7 and check its current alias.
$node = \Drupal\node\Entity\Node::load(7);
if ($node) {
  echo "Node 7 title: " . $node->label() . "\n";
  echo "Node 7 path alias: " . ($node->get('path')->alias ?? '(none)') . "\n";

  // Remove the alias.
  $node->set('path', ['alias' => '']);
  $node->save();
  echo "✓ Alias removed from node 7.\n";
} else {
  echo "Node 7 not found.\n";
}

// Verify.
$rows_after = $db->select('path_alias', 'pa')
  ->fields('pa', ['id', 'path', 'alias'])
  ->condition('alias', '/events')
  ->execute()
  ->fetchAll();

if (empty($rows_after)) {
  echo "✓ /events alias is gone.\n";
} else {
  echo "Still exists: ";
  foreach ($rows_after as $r) {
    echo " id={$r->id} path={$r->path}";
  }
  echo "\n";
}
