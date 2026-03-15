<?php

namespace Drupal\open_web_exchange;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;

/**
 * Service for querying events with filtering and sorting.
 */
class EventQueryService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected Connection $database,
    protected $dateTime,
  ) {}

  /**
   * Query upcoming events with optional filters.
   *
   * @param array $filters
   *   Optional filters:
   *   - topic_ids: array of taxonomy term IDs
   *   - format: 'in_person', 'virtual', or 'hybrid'
   *   - from_date: ISO 8601 date string
   *   - to_date: ISO 8601 date string
   *   - limit: maximum results (default 10)
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of Event nodes.
   */
  public function queryUpcomingEvents(array $filters = []): array {
    $storage = $this->entityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->condition('type', 'event')
      ->condition('status', 1)
      ->sort('field_event__date', 'ASC')
      ->accessCheck(FALSE);

    // smartdate stores Unix timestamps; convert ISO string to timestamp.
    $from_ts = isset($filters['from_date'])
      ? strtotime($filters['from_date'])
      : time();
    $query->condition('field_event__date', $from_ts, '>=');

    if (!empty($filters['to_date'])) {
      $query->condition('field_event__date', strtotime($filters['to_date']), '<=');
    }

    if (!empty($filters['format'])) {
      $query->condition('field_event_format', $filters['format']);
    }

    if (!empty($filters['topic_ids'])) {
      $query->condition('field_tags', $filters['topic_ids'], 'IN');
    }

    $limit = $filters['limit'] ?? 10;
    $query->range(0, $limit);

    $nids = $query->execute();
    if (empty($nids)) {
      return [];
    }

    return $storage->loadMultiple($nids);
  }

  /**
   * Get a single event by node ID.
   *
   * @param int $nid
   *   The node ID.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The event node or NULL if not found.
   */
  public function getEventById(int $nid): ?object {
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if ($node && $node->bundle() === 'event') {
      return $node;
    }
    return NULL;
  }

  /**
   * Format an event node as a structured data array for MCP responses.
   *
   * @param \Drupal\node\NodeInterface $event
   *   The event node.
   *
   * @return array
   *   Structured event data.
   */
  public function formatEventForMcp(object $event): array {
    $topics = [];
    foreach ($event->get('field_tags')->referencedEntities() as $term) {
      $topics[] = ['id' => $term->id(), 'name' => $term->label()];
    }

    $speakers = [];
    foreach ($event->get('field_speakers')->referencedEntities() as $speaker) {
      $org = $speaker->get('field_organization')->value ?? '';
      $speakers[] = [
        'id' => $speaker->id(),
        'name' => $speaker->label(),
        'organization' => $org,
      ];
    }

    // smartdate stores Unix timestamps in value / end_value.
    $date_item = $event->get('field_event__date')->first();
    $start_ts = $date_item?->value;
    $end_ts = $date_item?->end_value;
    $start = $start_ts ? date('c', (int) $start_ts) : NULL;
    $end = $end_ts ? date('c', (int) $end_ts) : NULL;

    $limit = $event->get('field_registration_limit')->value;
    $link_field = $event->get('field_event__link');
    $virtual_link = $link_field->isEmpty() ? '' : $link_field->first()->uri;

    return [
      'id' => (int) $event->id(),
      'title' => $event->label(),
      'description' => $event->get('field_content')->value ?? '',
      'start_date' => $start,
      'end_date' => $end,
      'format' => $event->get('field_event_format')->value,
      'location' => $event->get('field_event__location_name')->value ?? '',
      'virtual_link' => $virtual_link,
      'topics' => $topics,
      'speakers' => $speakers,
      'registration_limit' => $limit ? (int) $limit : NULL,
      'url' => $event->toUrl('canonical', ['absolute' => TRUE])->toString(),
    ];
  }

}
