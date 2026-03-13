<?php

namespace Drupal\open_web_exchange\Plugin\McpTool;

use Drupal\mcp\Plugin\McpToolBase;
use Drupal\open_web_exchange\RecommendationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MCP tool for suggesting personalised events to a member.
 *
 * Matches upcoming events to a user's topic interests from their
 * Member Profile, excluding events they are already registered for.
 *
 * @McpTool(
 *   id = "suggest_events",
 *   label = @Translation("Suggest Events"),
 *   description = @Translation("Return personalised event recommendations for a member based on their topic interests. Excludes events the member is already registered for."),
 * )
 */
class SuggestEventsTool extends McpToolBase {

  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    protected RecommendationService $recommendationService,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('open_web_exchange.recommendation_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInputSchema(): array {
    return [
      'type' => 'object',
      'required' => ['user_id'],
      'properties' => [
        'user_id' => [
          'type' => 'integer',
          'description' => 'The Drupal user account ID of the member to generate recommendations for.',
        ],
        'limit' => [
          'type' => 'integer',
          'description' => 'Maximum number of recommendations to return (default 5, max 20).',
          'minimum' => 1,
          'maximum' => 20,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array $input): array {
    $user_id = (int) ($input['user_id'] ?? 0);

    if (!$user_id) {
      return ['error' => 'user_id is required.'];
    }

    $limit = min((int) ($input['limit'] ?? 5), 20);
    $recommendations = $this->recommendationService->suggestEventsForUser($user_id, $limit);

    return [
      'user_id' => $user_id,
      'count' => count($recommendations),
      'recommendations' => $recommendations,
    ];
  }

}
