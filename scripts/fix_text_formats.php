<?php

/**
 * Fix text formats and field content placement.
 *
 * - Fixes homepage (node/2): changes field_content format from full_html to content_format
 * - Fixes person nodes: moves HTML bio from field_description to field_content
 * - Fixes event nodes: moves HTML description from field_description to field_content
 *
 * Run: ddev exec drush php:script /var/www/html/scripts/fix_text_formats.php
 */

use Drupal\node\Entity\Node;

$entity_type_manager = \Drupal::entityTypeManager();
$node_storage = $entity_type_manager->getStorage('node');

// ──────────────────────────────────────────────
// 1. Fix homepage (nid 2): wrong text format
// ──────────────────────────────────────────────
$homepage = $node_storage->load(2);
if ($homepage) {
  $field_content = $homepage->get('field_content');
  if (!$field_content->isEmpty()) {
    $value = $field_content->value;
    $homepage->set('field_content', [
      'value'  => $value,
      'format' => 'content_format',
    ]);
    $homepage->save();
    echo "✓ Homepage (nid 2): fixed field_content format → content_format\n";
  } else {
    echo "  Homepage (nid 2): field_content is empty, skipping\n";
  }
}

// ──────────────────────────────────────────────
// 2. Fix person nodes: move HTML bio to field_content
// ──────────────────────────────────────────────
$person_nids = $node_storage->getQuery()
  ->condition('type', 'person')
  ->accessCheck(FALSE)
  ->execute();

foreach ($person_nids as $nid) {
  $node = $node_storage->load($nid);
  if (!$node) {
    continue;
  }

  $desc_field = $node->get('field_description');
  $desc_value = $desc_field->isEmpty() ? '' : $desc_field->value;

  // Only migrate if the description contains HTML tags.
  if (empty($desc_value) || strip_tags($desc_value) === $desc_value) {
    echo "  Person nid {$nid}: no HTML in field_description, skipping\n";
    continue;
  }

  // Put HTML into field_content (formatted text).
  $node->set('field_content', [
    'value'  => $desc_value,
    'format' => 'content_format',
  ]);

  // Put plain-text summary into field_description (plain string_long).
  $plain = strip_tags($desc_value);
  $plain = preg_replace('/\s+/', ' ', trim($plain));
  $node->set('field_description', mb_substr($plain, 0, 200));

  $node->save();
  echo "✓ Person nid {$nid} ({$node->label()}): moved HTML bio to field_content\n";
}

// ──────────────────────────────────────────────
// 3. Fix event nodes: move HTML description to field_content
// ──────────────────────────────────────────────
$event_nids = $node_storage->getQuery()
  ->condition('type', 'event')
  ->accessCheck(FALSE)
  ->execute();

foreach ($event_nids as $nid) {
  $node = $node_storage->load($nid);
  if (!$node) {
    continue;
  }

  $desc_field = $node->get('field_description');
  $desc_value = $desc_field->isEmpty() ? '' : $desc_field->value;

  // Only migrate if description contains HTML.
  if (empty($desc_value) || strip_tags($desc_value) === $desc_value) {
    echo "  Event nid {$nid}: no HTML in field_description, skipping\n";
    continue;
  }

  // Put HTML into field_content — but only if field_content is currently empty.
  $content_field = $node->get('field_content');
  if ($content_field->isEmpty()) {
    $node->set('field_content', [
      'value'  => $desc_value,
      'format' => 'content_format',
    ]);
  }

  // Plain-text summary stays in field_description.
  $plain = strip_tags($desc_value);
  $plain = preg_replace('/\s+/', ' ', trim($plain));
  $node->set('field_description', mb_substr($plain, 0, 200));

  $node->save();
  echo "✓ Event nid {$nid} ({$node->label()}): moved HTML description to field_content\n";
}

echo "\nDone.\n";
