<?php

namespace Drupal\open_web_exchange;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service for generating personalized event recommendations.
 *
 * Matches events to users based on topic interests recorded
 * in their Member Profile, excluding events already registered for.
 */
class RecommendationService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EventQueryService $eventQueryService,
  ) {}

  /**
   * Suggest events for a user based on their topic interests.
   *
   * @param int $user_id
   *   The Drupal user account ID.
   * @param int $limit
   *   Maximum number of recommendations to return.
   *
   * @return array
   *   Array of formatted event data arrays, scored by topic overlap.
   */
  public function suggestEventsForUser(int $user_id, int $limit = 5): array {
    // Load the member profile for this user.
    $profile = $this->getMemberProfile($user_id);

    if (!$profile) {
      // Fall back to upcoming events with no personalisation.
      $events = $this->eventQueryService->queryUpcomingEvents(['limit' => $limit]);
      return array_map(
        fn($e) => $this->eventQueryService->formatEventForMcp($e) + ['score' => 0, 'match_reason' => 'upcoming'],
        $events
      );
    }

    // Collect the user's topic interest IDs.
    $interest_ids = [];
    foreach ($profile->get('field_interests')->referencedEntities() as $term) {
      $interest_ids[] = (int) $term->id();
    }

    // Get already-registered event IDs so we don't re-suggest them.
    $registered_nids = $this->getRegisteredEventIds($user_id);

    // Query upcoming events, optionally filtered by interests.
    $query_filters = ['limit' => 50];
    if (!empty($interest_ids)) {
      $query_filters['topic_ids'] = $interest_ids;
    }
    $events = $this->eventQueryService->queryUpcomingEvents($query_filters);

    // Score and rank.
    $scored = [];
    foreach ($events as $event) {
      if (in_array((int) $event->id(), $registered_nids)) {
        continue;
      }

      $event_topic_ids = [];
      foreach ($event->get('field_topics')->referencedEntities() as $term) {
        $event_topic_ids[] = (int) $term->id();
      }

      $overlap = count(array_intersect($interest_ids, $event_topic_ids));
      $formatted = $this->eventQueryService->formatEventForMcp($event);
      $formatted['score'] = $overlap;
      $formatted['match_reason'] = $overlap > 0
        ? sprintf('Matches %d of your topic interests', $overlap)
        : 'Upcoming event';

      $scored[] = $formatted;
    }

    // Sort by score descending, then by date ascending.
    usort($scored, function (array $a, array $b) {
      if ($b['score'] !== $a['score']) {
        return $b['score'] <=> $a['score'];
      }
      return strcmp($a['start_date'] ?? '', $b['start_date'] ?? '');
    });

    return array_slice($scored, 0, $limit);
  }

  /**
   * Load the member profile node for a given user account.
   *
   * Profiles are linked by the node author (uid field).
   *
   * @param int $user_id
   *   Drupal user account ID.
   *
   * @return \Drupal\node\NodeInterface|null
   */
  protected function getMemberProfile(int $user_id): ?object {
    $storage = $this->entityTypeManager->getStorage('node');
    $nids = $storage->getQuery()
      ->condition('type', 'member_profile')
      ->condition('uid', $user_id)
      ->condition('status', 1)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    if (empty($nids)) {
      return NULL;
    }

    return $storage->load(reset($nids));
  }

  /**
   * Get the node IDs of events a user is already registered for.
   *
   * @param int $user_id
   *   Drupal user account ID.
   *
   * @return int[]
   */
  protected function getRegisteredEventIds(int $user_id): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $reg_nids = $storage->getQuery()
      ->condition('type', 'registration')
      ->condition('field_registrant', $user_id)
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->execute();

    if (empty($reg_nids)) {
      return [];
    }

    $registrations = $storage->loadMultiple($reg_nids);
    $event_ids = [];
    foreach ($registrations as $reg) {
      $event_ref = $reg->get('field_event_ref');
      if (!$event_ref->isEmpty()) {
        $event_ids[] = (int) $event_ref->target_id;
      }
    }
    return $event_ids;
  }

}
