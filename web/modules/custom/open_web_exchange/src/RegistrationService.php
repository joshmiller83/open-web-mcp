<?php

namespace Drupal\open_web_exchange;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\NodeInterface;

/**
 * Service for managing event registrations.
 *
 * Handles registration creation, cancellation, capacity checks,
 * and user registration history.
 */
class RegistrationService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected AccountInterface $currentUser,
    protected MessengerInterface $messenger,
  ) {}

  /**
   * Register a user for an event.
   *
   * @param int $event_nid
   *   The event node ID.
   * @param int $user_id
   *   The user account ID.
   *
   * @return array
   *   Result with keys 'success' (bool) and 'message' (string).
   */
  public function registerUserForEvent(int $event_nid, int $user_id): array {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $event = $node_storage->load($event_nid);

    if (!$event || $event->bundle() !== 'event') {
      return ['success' => FALSE, 'message' => "Event $event_nid not found."];
    }

    if (!$event->isPublished()) {
      return ['success' => FALSE, 'message' => 'This event is not currently accepting registrations.'];
    }

    // Check if already registered.
    if ($this->isUserRegistered($event_nid, $user_id)) {
      return ['success' => FALSE, 'message' => 'You are already registered for this event.'];
    }

    // Check capacity.
    $availability = $this->getAvailability($event);
    if ($availability['status'] === 'full') {
      return ['success' => FALSE, 'message' => 'This event has reached its registration limit.'];
    }

    // Create registration node (using a lightweight 'registration' bundle).
    $registration = $node_storage->create([
      'type' => 'registration',
      'title' => sprintf('Registration: %s — User %d', $event->label(), $user_id),
      'field_event_ref' => ['target_id' => $event_nid],
      'field_registrant' => ['target_id' => $user_id],
      'status' => 1,
    ]);
    $registration->save();

    return [
      'success' => TRUE,
      'message' => sprintf('Successfully registered for "%s".', $event->label()),
      'registration_id' => (int) $registration->id(),
    ];
  }

  /**
   * Check whether a user is already registered for an event.
   *
   * @param int $event_nid
   *   The event node ID.
   * @param int $user_id
   *   The user account ID.
   *
   * @return bool
   *   TRUE if the user is registered.
   */
  public function isUserRegistered(int $event_nid, int $user_id): bool {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'registration')
      ->condition('field_event_ref', $event_nid)
      ->condition('field_registrant', $user_id)
      ->condition('status', 1)
      ->count()
      ->accessCheck(FALSE);

    return (int) $query->execute() > 0;
  }

  /**
   * Get registration availability for an event.
   *
   * @param \Drupal\node\NodeInterface $event
   *   The event node.
   *
   * @return array
   *   Array with keys:
   *   - 'registered': number of current registrations
   *   - 'limit': registration limit (NULL = unlimited)
   *   - 'remaining': seats remaining (NULL = unlimited)
   *   - 'status': 'open', 'limited', or 'full'
   */
  public function getAvailability(object $event): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $count = (int) $storage->getQuery()
      ->condition('type', 'registration')
      ->condition('field_event_ref', $event->id())
      ->condition('status', 1)
      ->count()
      ->accessCheck(FALSE)
      ->execute();

    $limit = $event->get('field_registration_limit')->value;
    $limit = $limit ? (int) $limit : NULL;

    if ($limit === NULL) {
      return [
        'registered' => $count,
        'limit' => NULL,
        'remaining' => NULL,
        'status' => 'open',
      ];
    }

    $remaining = $limit - $count;

    return [
      'registered' => $count,
      'limit' => $limit,
      'remaining' => max(0, $remaining),
      'status' => $remaining <= 0 ? 'full' : ($remaining <= 5 ? 'limited' : 'open'),
    ];
  }

  /**
   * Get all events a user has registered for.
   *
   * @param int $user_id
   *   The user account ID.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of registered Event nodes.
   */
  public function getUserRegistrations(int $user_id): array {
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
    $events = [];
    foreach ($registrations as $reg) {
      $event = $reg->get('field_event_ref')->entity;
      if ($event) {
        $events[$event->id()] = $event;
      }
    }
    return $events;
  }

}
