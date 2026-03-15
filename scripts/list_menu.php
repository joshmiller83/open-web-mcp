<?php
// List all main menu links.
$storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
$links = $storage->loadByProperties(['menu_name' => 'main']);
foreach ($links as $link) {
  echo "id={$link->id()} title={$link->label()} uri={$link->link->uri} weight={$link->getWeight()}\n";
}

// Also check the menu_link_manager for all links.
echo "\n--- All menu links from menu.link_tree ---\n";
$tree_params = new \Drupal\Core\Menu\MenuTreeParameters();
$tree_params->setMaxDepth(1);
$tree = \Drupal::menuTree()->load('main', $tree_params);
foreach ($tree as $element) {
  $link = $element->link;
  echo "id=" . $link->getPluginId() . " title=" . $link->getTitle() . " url=" . $link->getUrlObject()->toString() . " weight=" . $link->getWeight() . "\n";
}
