<?php
foreach (\Drupal::entityTypeManager()->getStorage('menu_link_content')->loadMultiple() as $l) {
  echo $l->id() . ' | ' . $l->getMenuName() . ' | ' . $l->getTitle() . ' | ' . $l->link->uri . PHP_EOL;
}
