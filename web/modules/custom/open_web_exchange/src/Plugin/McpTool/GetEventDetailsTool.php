<?php

namespace Drupal\open_web_exchange\Plugin\McpTool;

use Drupal\mcp\Plugin\McpToolBase;
use Drupal\open_web_exchange\EventQueryService;
use Drupal\open_web_exchange\RegistrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MCP tool for retrieving full details of a single event.
 *
 * Returns event metadata, schedule, speakers, topics, and
 * current registration availability.
 *
 * @McpTool(
 *   id = "get_event_details",
 *   label = @Translation("Get Event Details"),
 *   description = @Translation("Retrieve full details for a specific Open Web Exchange event, including schedule, speakers, topics, location, and registration availability."),
 * )
 */
class GetEventDetailsTool extends McpToolBase {

  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    protected EventQueryService $eventQueryService,
    protected RegistrationService $registrationService,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('open_web_exchange.event_query_service'),
      $container->get('open_web_exchange.registration_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInputSchema(): array {
    return [
      'type' => 'object',
      'required' => ['event_id'],
      'properties' => [
        'event_id' => [
          'type' => 'integer',
          'description' => 'The numeric node ID of the event to retrieve.',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array $input): array {
    $event_id = (int) ($input['event_id'] ?? 0);

    if (!$event_id) {
      return ['error' => 'event_id is required.'];
    }

    $event = $this->eventQueryService->getEventById($event_id);
    if (!$event) {
      return ['error' => "No event found with ID $event_id."];
    }

    $data = $this->eventQueryService->formatEventForMcp($event);

    // Add schedule text.
    $schedule = $event->get('field_event_schedule')->value;
    $data['schedule'] = $schedule ?? '';

    // Add live registration availability.
    $availability = $this->registrationService->getAvailability($event);
    $data['availability'] = $availability;

    return $data;
  }

}
