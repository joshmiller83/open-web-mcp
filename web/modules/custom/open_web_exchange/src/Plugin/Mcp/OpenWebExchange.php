<?php

namespace Drupal\open_web_exchange\Plugin\Mcp;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\mcp\Attribute\Mcp;
use Drupal\mcp\Plugin\McpPluginBase;
use Drupal\mcp\ServerFeatures\Tool;
use Drupal\open_web_exchange\EventQueryService;
use Drupal\open_web_exchange\RecommendationService;
use Drupal\open_web_exchange\RegistrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Open Web Exchange MCP plugin.
 *
 * Exposes event querying, registration, and recommendation tools
 * via the Model Context Protocol.
 */
#[Mcp(
  id: 'open-web-exchange',
  name: new TranslatableMarkup('Open Web Exchange'),
  description: new TranslatableMarkup('Event management and registration tools for the Open Web Exchange platform.'),
)]
class OpenWebExchange extends McpPluginBase {

  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    protected EventQueryService $eventQueryService,
    protected RegistrationService $registrationService,
    protected RecommendationService $recommendationService,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('open_web_exchange.event_query_service'),
      $container->get('open_web_exchange.registration_service'),
      $container->get('open_web_exchange.recommendation_service'),
    );
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'enabled' => TRUE,
      'roles'   => [],
      'config'  => [],
      'tools'   => [],
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Returns empty array so any authenticated or anonymous user with the
   * 'use mcp server' permission can access these public event tools.
   */
  public function getAllowedRoles(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getTools(): array {
    return [
      new Tool(
        name: 'query_events',
        description: 'Search Open Web Exchange events by topic, format, or date range. Returns a list of matching upcoming events.',
        inputSchema: [
          'type' => 'object',
          'properties' => [
            'topic' => [
              'type' => 'string',
              'description' => 'Filter by topic name (partial match, e.g. "Open Data", "Climate").',
            ],
            'format' => [
              'type' => 'string',
              'enum' => ['in_person', 'virtual', 'hybrid'],
              'description' => 'Filter by delivery format.',
            ],
            'from_date' => [
              'type' => 'string',
              'description' => 'Return events on or after this date (ISO 8601, e.g. "2025-06-01T00:00:00").',
            ],
            'limit' => [
              'type' => 'integer',
              'description' => 'Maximum number of results (default 10, max 50).',
            ],
          ],
        ],
      ),
      new Tool(
        name: 'get_event_details',
        description: 'Get full details for a single event including schedule, speakers, topics, and live registration availability.',
        inputSchema: [
          'type' => 'object',
          'required' => ['event_id'],
          'properties' => [
            'event_id' => [
              'type' => 'integer',
              'description' => 'The node ID of the event.',
            ],
          ],
        ],
      ),
      new Tool(
        name: 'register_for_event',
        description: 'Register a member for an event. Validates capacity and prevents duplicate registrations.',
        inputSchema: [
          'type' => 'object',
          'required' => ['event_id', 'user_id'],
          'properties' => [
            'event_id' => [
              'type' => 'integer',
              'description' => 'The node ID of the event.',
            ],
            'user_id' => [
              'type' => 'integer',
              'description' => 'The Drupal user account ID of the member to register.',
            ],
          ],
        ],
      ),
      new Tool(
        name: 'suggest_events',
        description: 'Return personalised event recommendations for a member based on their topic interests. Excludes already-registered events.',
        inputSchema: [
          'type' => 'object',
          'required' => ['user_id'],
          'properties' => [
            'user_id' => [
              'type' => 'integer',
              'description' => 'The Drupal user account ID.',
            ],
            'limit' => [
              'type' => 'integer',
              'description' => 'Maximum recommendations to return (default 5).',
            ],
          ],
        ],
      ),
      new Tool(
        name: 'get_speaker_info',
        description: 'Retrieve a speaker or facilitator\'s profile including their organisation, sector, bio, and topic interests.',
        inputSchema: [
          'type' => 'object',
          'required' => ['profile_id'],
          'properties' => [
            'profile_id' => [
              'type' => 'integer',
              'description' => 'The node ID of the person profile.',
            ],
          ],
        ],
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function executeTool(string $toolId, mixed $arguments): array {
    $result = match ($toolId) {
      'query_events'       => $this->executeQueryEvents($arguments),
      'get_event_details'  => $this->executeGetEventDetails($arguments),
      'register_for_event' => $this->executeRegisterForEvent($arguments),
      'suggest_events'     => $this->executeSuggestEvents($arguments),
      'get_speaker_info'   => $this->executeGetSpeakerInfo($arguments),
      default              => ['error' => "Unknown tool: $toolId"],
    };

    return [
      'content' => [
        ['type' => 'text', 'text' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)],
      ],
    ];
  }

  /**
   * Execute query_events.
   */
  protected function executeQueryEvents(array $input): array {
    $filters = [];

    if (!empty($input['format'])) {
      $filters['format'] = $input['format'];
    }
    if (!empty($input['from_date'])) {
      $filters['from_date'] = $input['from_date'];
    }
    $filters['limit'] = min((int) ($input['limit'] ?? 10), 50);

    if (!empty($input['topic'])) {
      $term_ids = $this->findTagIdsByName($input['topic']);
      if (!empty($term_ids)) {
        $filters['topic_ids'] = $term_ids;
      }
    }

    $events = $this->eventQueryService->queryUpcomingEvents($filters);
    $results = array_map(
      fn($e) => $this->eventQueryService->formatEventForMcp($e),
      $events
    );

    return ['count' => count($results), 'events' => array_values($results)];
  }

  /**
   * Execute get_event_details.
   */
  protected function executeGetEventDetails(array $input): array {
    $event_id = (int) ($input['event_id'] ?? 0);
    if (!$event_id) {
      return ['error' => 'event_id is required.'];
    }

    $event = $this->eventQueryService->getEventById($event_id);
    if (!$event) {
      return ['error' => "No event found with ID $event_id."];
    }

    $data = $this->eventQueryService->formatEventForMcp($event);
    $data['schedule'] = $event->get('field_event_schedule')->value ?? '';
    $data['availability'] = $this->registrationService->getAvailability($event);

    return $data;
  }

  /**
   * Execute register_for_event.
   */
  protected function executeRegisterForEvent(array $input): array {
    $event_id = (int) ($input['event_id'] ?? 0);
    $user_id = (int) ($input['user_id'] ?? 0);

    if (!$event_id || !$user_id) {
      return ['success' => FALSE, 'message' => 'Both event_id and user_id are required.'];
    }

    return $this->registrationService->registerUserForEvent($event_id, $user_id);
  }

  /**
   * Execute suggest_events.
   */
  protected function executeSuggestEvents(array $input): array {
    $user_id = (int) ($input['user_id'] ?? 0);
    if (!$user_id) {
      return ['error' => 'user_id is required.'];
    }

    $limit = min((int) ($input['limit'] ?? 5), 20);
    $recommendations = $this->recommendationService->suggestEventsForUser($user_id, $limit);

    return [
      'user_id' => $user_id,
      'count'   => count($recommendations),
      'recommendations' => $recommendations,
    ];
  }

  /**
   * Execute get_speaker_info.
   */
  protected function executeGetSpeakerInfo(array $input): array {
    $profile_id = (int) ($input['profile_id'] ?? 0);
    if (!$profile_id) {
      return ['error' => 'profile_id is required.'];
    }

    $node = \Drupal::entityTypeManager()->getStorage('node')->load($profile_id);
    if (!$node || $node->bundle() !== 'person') {
      return ['error' => "No person profile found with ID $profile_id."];
    }

    $interests = [];
    foreach ($node->get('field_tags')->referencedEntities() as $term) {
      $interests[] = ['id' => (int) $term->id(), 'name' => $term->label()];
    }

    return [
      'id'           => (int) $node->id(),
      'name'         => $node->label(),
      'organization' => $node->get('field_organization')->value ?? '',
      'sector'       => $node->get('field_sector')->value ?? '',
      'bio'          => $node->get('field_content')->value ?? '',
      'interests'    => $interests,
      'url'          => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
    ];
  }

  /**
   * Find tag term IDs by partial name match.
   */
  protected function findTagIdsByName(string $name): array {
    $tids = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery()
      ->condition('vid', 'tags')
      ->condition('name', '%' . $name . '%', 'LIKE')
      ->accessCheck(FALSE)
      ->execute();
    return array_map('intval', array_values($tids));
  }

}
