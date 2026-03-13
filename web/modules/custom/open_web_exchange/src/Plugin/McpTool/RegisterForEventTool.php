<?php

namespace Drupal\open_web_exchange\Plugin\McpTool;

use Drupal\mcp\Plugin\McpToolBase;
use Drupal\open_web_exchange\RegistrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MCP tool for registering a user for an event.
 *
 * Validates capacity, prevents duplicate registrations,
 * and returns a confirmation with the registration ID.
 *
 * @McpTool(
 *   id = "register_for_event",
 *   label = @Translation("Register for Event"),
 *   description = @Translation("Register a member for an Open Web Exchange event. Checks capacity and prevents duplicate registrations. Returns a confirmation or an error message."),
 * )
 */
class RegisterForEventTool extends McpToolBase {

  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    protected RegistrationService $registrationService,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('open_web_exchange.registration_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInputSchema(): array {
    return [
      'type' => 'object',
      'required' => ['event_id', 'user_id'],
      'properties' => [
        'event_id' => [
          'type' => 'integer',
          'description' => 'The node ID of the event to register for.',
        ],
        'user_id' => [
          'type' => 'integer',
          'description' => 'The Drupal user account ID of the member to register.',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array $input): array {
    $event_id = (int) ($input['event_id'] ?? 0);
    $user_id = (int) ($input['user_id'] ?? 0);

    if (!$event_id || !$user_id) {
      return ['success' => FALSE, 'message' => 'Both event_id and user_id are required.'];
    }

    return $this->registrationService->registerUserForEvent($event_id, $user_id);
  }

}
