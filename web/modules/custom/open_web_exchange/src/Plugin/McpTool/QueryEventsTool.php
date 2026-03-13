<?php

namespace Drupal\open_web_exchange\Plugin\McpTool;

use Drupal\mcp\Plugin\McpToolBase;
use Drupal\open_web_exchange\EventQueryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MCP tool for querying Open Web Exchange events.
 *
 * Allows AI tools to search upcoming events with filters for topic,
 * format, and date range.
 *
 * @McpTool(
 *   id = "query_events",
 *   label = @Translation("Query Events"),
 *   description = @Translation("Search Open Web Exchange events by topic, format, or date range. Returns a list of matching upcoming events with full details."),
 * )
 */
class QueryEventsTool extends McpToolBase {

  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    protected EventQueryService $eventQueryService,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('open_web_exchange.event_query_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInputSchema(): array {
    return [
      'type' => 'object',
      'properties' => [
        'topic' => [
          'type' => 'string',
          'description' => 'Filter events by topic name (e.g. "Open Data", "Community Health"). Partial matches accepted.',
        ],
        'topic_id' => [
          'type' => 'integer',
          'description' => 'Filter events by exact taxonomy term ID for a topic.',
        ],
        'format' => [
          'type' => 'string',
          'enum' => ['in_person', 'virtual', 'hybrid'],
          'description' => 'Filter by delivery format: in_person, virtual, or hybrid.',
        ],
        'from_date' => [
          'type' => 'string',
          'description' => 'Return events starting on or after this date (ISO 8601, e.g. "2025-06-01T00:00:00").',
        ],
        'to_date' => [
          'type' => 'string',
          'description' => 'Return events starting on or before this date (ISO 8601).',
        ],
        'limit' => [
          'type' => 'integer',
          'description' => 'Maximum number of events to return (default 10, max 50).',
          'minimum' => 1,
          'maximum' => 50,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array $input): array {
    $filters = [];

    if (!empty($input['format'])) {
      $filters['format'] = $input['format'];
    }
    if (!empty($input['from_date'])) {
      $filters['from_date'] = $input['from_date'];
    }
    if (!empty($input['to_date'])) {
      $filters['to_date'] = $input['to_date'];
    }
    $filters['limit'] = min((int) ($input['limit'] ?? 10), 50);

    // Resolve topic name to term ID(s).
    if (!empty($input['topic_id'])) {
      $filters['topic_ids'] = [(int) $input['topic_id']];
    }
    elseif (!empty($input['topic'])) {
      $term_ids = $this->findTopicIdsByName($input['topic']);
      if (!empty($term_ids)) {
        $filters['topic_ids'] = $term_ids;
      }
    }

    $events = $this->eventQueryService->queryUpcomingEvents($filters);
    $results = array_map(
      fn($e) => $this->eventQueryService->formatEventForMcp($e),
      $events
    );

    return [
      'count' => count($results),
      'events' => array_values($results),
    ];
  }

  /**
   * Find topic term IDs by partial name match.
   *
   * @param string $name
   *   Topic name to search.
   *
   * @return int[]
   *   Matching term IDs.
   */
  protected function findTopicIdsByName(string $name): array {
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $query = $storage->getQuery()
      ->condition('vid', 'topics')
      ->condition('name', '%' . $name . '%', 'LIKE')
      ->accessCheck(FALSE);

    $tids = $query->execute();
    return array_map('intval', array_values($tids));
  }

}
