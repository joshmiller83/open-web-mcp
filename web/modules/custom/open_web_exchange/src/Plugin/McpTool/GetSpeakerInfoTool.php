<?php

namespace Drupal\open_web_exchange\Plugin\McpTool;

use Drupal\mcp\Plugin\McpToolBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MCP tool for retrieving speaker and facilitator information.
 *
 * Returns the Member Profile details for a given speaker, including
 * their organisation, sector, and topic interests.
 *
 * @McpTool(
 *   id = "get_speaker_info",
 *   label = @Translation("Get Speaker Info"),
 *   description = @Translation("Retrieve the profile, background, and topic interests for an Open Web Exchange speaker or facilitator."),
 * )
 */
class GetSpeakerInfoTool extends McpToolBase {

  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInputSchema(): array {
    return [
      'type' => 'object',
      'required' => ['profile_id'],
      'properties' => [
        'profile_id' => [
          'type' => 'integer',
          'description' => 'The node ID of the Member Profile to retrieve.',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array $input): array {
    $profile_id = (int) ($input['profile_id'] ?? 0);

    if (!$profile_id) {
      return ['error' => 'profile_id is required.'];
    }

    $node = $this->entityTypeManager->getStorage('node')->load($profile_id);
    if (!$node || $node->bundle() !== 'member_profile') {
      return ['error' => "No member profile found with ID $profile_id."];
    }

    $interests = [];
    foreach ($node->get('field_interests')->referencedEntities() as $term) {
      $interests[] = ['id' => (int) $term->id(), 'name' => $term->label()];
    }

    return [
      'id' => (int) $node->id(),
      'name' => $node->label(),
      'organization' => $node->get('field_organization')->value ?? '',
      'sector' => $node->get('field_sector')->value ?? '',
      'bio' => $node->get('body')->value ?? '',
      'interests' => $interests,
      'url' => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
    ];
  }

}
