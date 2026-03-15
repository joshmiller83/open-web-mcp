<?php

/**
 * Fix main menu weights and remove duplicate/unwanted links.
 *
 * Goals:
 *   - Home (internal:/) → weight -10 (first)
 *   - Events via view auto-link → weight 0 (second) — not touched, already 0
 *   - Remove the static Events link pointing to entity:node/7
 *   - People → weight 10 (third)
 *   - Delete any "Member" link
 *
 * Run: ddev exec drush php:script /var/www/html/scripts/fix_menu.php
 */

use Drupal\menu_link_content\Entity\MenuLinkContent;

$entity_type_manager = \Drupal::entityTypeManager();
$menu_link_storage = $entity_type_manager->getStorage('menu_link_content');

// Load all main menu links.
$links = $menu_link_storage->loadByProperties(['menu_name' => 'main']);

$home_link   = NULL;
$people_link = NULL;
$to_delete   = [];

foreach ($links as $link) {
  $title = $link->label();
  $uri   = $link->link->uri ?? '';

  echo "  Found: [{$link->id()}] \"{$title}\" → {$uri} (weight {$link->getWeight()})\n";

  // Home link.
  if ($uri === 'internal:/') {
    $home_link = $link;
  }

  // Static Events → node/7 (the one the view replaces).
  if (strpos($uri, 'entity:node/7') !== FALSE) {
    $to_delete[] = $link;
  }

  // People link.
  if (strpos($uri, 'entity:node/11') !== FALSE || strtolower($title) === 'people') {
    $people_link = $link;
  }

  // Member link (unwanted, may exist on Pantheon).
  if (strtolower($title) === 'member') {
    $to_delete[] = $link;
  }
}

// Delete unwanted links.
foreach ($to_delete as $link) {
  echo "✗ Deleting [{$link->id()}] \"{$link->label()}\" → {$link->link->uri}\n";
  $link->delete();
}

// Fix Home weight.
if ($home_link) {
  $home_link->set('weight', -10);
  $home_link->save();
  echo "✓ Home: weight set to -10\n";
}

// Fix People weight.
if ($people_link) {
  $people_link->set('weight', 10);
  $people_link->save();
  echo "✓ People: weight set to 10\n";
}

echo "\nDone. Clear caches for menu changes to appear:\n";
echo "  ddev exec drush cr\n";
