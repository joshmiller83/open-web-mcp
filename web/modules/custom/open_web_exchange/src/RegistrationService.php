<?php

namespace Drupal\open_web_exchange;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service for managing event registrations via Webform submissions.
 */
class RegistrationService {

  const WEBFORM_ID = 'event_registration';

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected Connection $database,
  ) {}

  /**
   * Register a guest (anonymous) for an event.
   *
   * @param int $event_nid
   *   The event node ID.
   * @param string $name
   *   The registrant's full name.
   * @param string $phone
   *   The registrant's phone number.
   * @param int $attendee_count
   *   Number of people expected to attend.
   *
   * @return array
   *   Result with keys 'success', 'message', and optionally 'join_link'.
   */
  public function registerGuestForEvent(int $event_nid, string $name, string $phone, int $attendee_count = 1): array {
    $event = $this->entityTypeManager->getStorage('node')->load($event_nid);

    if (!$event || $event->bundle() !== 'event') {
      return ['success' => FALSE, 'message' => "Event $event_nid not found."];
    }

    if (!$event->isPublished()) {
      return ['success' => FALSE, 'message' => 'This event is not currently accepting registrations.'];
    }

    $availability = $this->getAvailability($event);
    if ($availability['status'] === 'full') {
      return ['success' => FALSE, 'message' => 'This event has reached its registration limit.'];
    }

    $webform = $this->entityTypeManager->getStorage('webform')->load(self::WEBFORM_ID);
    if (!$webform) {
      return ['success' => FALSE, 'message' => 'Registration form not available.'];
    }

    $submission = $this->entityTypeManager->getStorage('webform_submission')->create([
      'webform_id' => self::WEBFORM_ID,
      'data' => [
        'event_id'       => $event_nid,
        'name'           => $name,
        'phone'          => $phone,
        'attendee_count' => $attendee_count,
      ],
    ]);
    $submission->save();

    $result = [
      'success'         => TRUE,
      'message'         => sprintf('Successfully registered "%s" for "%s".', $name, $event->label()),
      'registration_id' => (int) $submission->id(),
    ];

    // Return the virtual join link for remote/hybrid events.
    $format = $event->get('field_event_format')->value ?? '';
    if (in_array($format, ['virtual', 'hybrid'])) {
      $link = $event->get('field_event__link')->first();
      if ($link) {
        $result['join_link']       = $link->uri;
        $result['join_link_title'] = $link->title ?: 'Join virtual session';
      }
    }

    return $result;
  }

  /**
   * Get registration availability for an event.
   *
   * @param \Drupal\node\NodeInterface $event
   *   The event node.
   *
   * @return array
   *   Array with keys 'registered', 'limit', 'remaining', 'status'.
   */
  public function getAvailability(object $event): array {
    // webform_submission_data stores each element as a row, so query directly.
    $count = (int) $this->database->select('webform_submission_data', 'wsd')
      ->condition('wsd.webform_id', self::WEBFORM_ID)
      ->condition('wsd.name', 'event_id')
      ->condition('wsd.value', (string) $event->id())
      ->countQuery()
      ->execute()
      ->fetchField();

    $limit = $event->get('field_registration_limit')->value;
    $limit = $limit ? (int) $limit : NULL;

    if ($limit === NULL) {
      return [
        'registered' => $count,
        'limit'      => NULL,
        'remaining'  => NULL,
        'status'     => 'open',
      ];
    }

    $remaining = $limit - $count;

    return [
      'registered' => $count,
      'limit'      => $limit,
      'remaining'  => max(0, $remaining),
      'status'     => $remaining <= 0 ? 'full' : ($remaining <= 5 ? 'limited' : 'open'),
    ];
  }

}
