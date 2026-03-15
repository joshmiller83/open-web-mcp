<?php
/**
 * Step 2: Clean up main navigation.
 * Remove Blog (id 11), Case studies (id 12), News (id 14) from main menu.
 */

$storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');

// IDs to remove from main menu
$remove_ids = [11, 12, 14];

foreach ($remove_ids as $id) {
  $link = $storage->load($id);
  if ($link) {
    echo "Deleting menu link: " . $link->getTitle() . " (id {$id})\n";
    $link->delete();
  } else {
    echo "Menu link id {$id} not found\n";
  }
}

// Update Home link to point to internal:/
$home = $storage->load(6);
if ($home) {
  $home->set('link', ['uri' => 'internal:/']);
  $home->save();
  echo "Updated Home link to internal:/\n";
}

echo "Menu cleanup complete.\n";
