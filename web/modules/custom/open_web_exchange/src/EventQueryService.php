<?php

namespace Drupal\open_web_exchange;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

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
      ->sort('field_event_date', 'ASC')
      ->accessCheck(FALSE);

    // Default to events from now onward.
    $from_date = $filters['from_date'] ?? date(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
    $query->condition('field_event_date', $from_date, '>=');

    if (!empty($filters['to_date'])) {
      $query->condition('field_event_date', $filters['to_date'], '<=');
    }

    if (!empty($filters['format'])) {
      $query->condition('field_event_format', $filters['format']);
    }

    if (!empty($filters['topic_ids'])) {
      $query->condition('field_topics', $filters['topic_ids'], 'IN');
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
    foreach ($event->get('field_topics')->referencedEntities() as $term) {
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

    $start = $event->get('field_event_date')->value;
    $end = $event->get('field_event_end_date')->value;
    $limit = $event->get('field_registration_limit')->value;

    return [
      'id' => (int) $event->id(),
      'title' => $event->label(),
      'description' => $event->get('body')->value ?? '',
      'start_date' => $start,
      'end_date' => $end,
      'format' => $event->get('field_event_format')->value,
      'location' => $event->get('field_event_location')->value ?? '',
      'virtual_link' => $event->get('field_virtual_link')->uri ?? '',
      'topics' => $topics,
      'speakers' => $speakers,
      'registration_limit' => $limit ? (int) $limit : NULL,
      'url' => $event->toUrl('canonical', ['absolute' => TRUE])->toString(),
    ];
  }

}
