<?php

/**
 * Delete the "Members" menu link from the main menu.
 */

$storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
$links = $storage->loadByProperties(['menu_name' => 'main']);

foreach ($links as $link) {
  $title = strtolower(trim($link->label()));
  if ($title === 'members' || $title === 'member') {
    echo "Deleting: [{$link->id()}] \"{$link->label()}\" → {$link->link->uri}\n";
    $link->delete();
  }
}

echo "Done.\n";
