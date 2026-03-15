<?php

namespace Drupal\open_web_exchange\Commands;

use Drush\Commands\DrushCommands;
use Drupal\open_web_exchange\EventQueryService;
use Drupal\open_web_exchange\RecommendationService;

/**
 * Drush commands for the Open Web Exchange demo.
 */
class OpenWebExchangeCommands extends DrushCommands {

  public function __construct(
    protected EventQueryService $eventQueryService,
    protected RecommendationService $recommendationService,
  ) {
    parent::__construct();
  }

  /**
   * List upcoming events, optionally filtered by topic.
   *
   * @param string $topic
   *   Optional topic name filter.
   * @param array $options
   *   Command options.
   *
   * @command owe:events
   * @aliases owe-events
   * @option format Filter by format: in_person, virtual, hybrid
   * @option limit Maximum results (default 10)
   * @usage drush owe:events "Open Data"
   *   List events tagged with the Open Data topic.
   * @usage drush owe:events --format=virtual
   *   List upcoming virtual events.
   */
  public function listEvents(string $topic = '', array $options = ['format' => NULL, 'limit' => 10]): void {
    $filters = ['limit' => (int) $options['limit']];
    if ($topic) {
      $filters['topic_ids'] = $this->findTopicIdsByName($topic);
    }
    if ($options['format']) {
      $filters['format'] = $options['format'];
    }

    $events = $this->eventQueryService->queryUpcomingEvents($filters);
    if (empty($events)) {
      $this->io()->writeln('<comment>No upcoming events found.</comment>');
      return;
    }

    $rows = [];
    foreach ($events as $event) {
      $data = $this->eventQueryService->formatEventForMcp($event);
      $topics = implode(', ', array_column($data['topics'], 'name'));
      $rows[] = [
        $data['id'],
        $data['title'],
        $data['start_date'],
        $data['format'],
        $topics ?: '—',
      ];
    }

    $this->io()->table(['ID', 'Title', 'Start', 'Format', 'Topics'], $rows);
  }

  /**
   * Show personalised event recommendations for a user.
   *
   * @param int $user_id
   *   The Drupal user account ID.
   * @param array $options
   *   Command options.
   *
   * @command owe:recommend
   * @aliases owe-rec
   * @option limit Number of recommendations (default 5)
   * @usage drush owe:recommend 2
   *   Show 5 recommendations for user ID 2.
   */
  public function recommend(int $user_id, array $options = ['limit' => 5]): void {
    $recs = $this->recommendationService->suggestEventsForUser($user_id, (int) $options['limit']);

    if (empty($recs)) {
      $this->io()->writeln('<comment>No recommendations found for this user.</comment>');
      return;
    }

    $rows = [];
    foreach ($recs as $r) {
      $topics = implode(', ', array_column($r['topics'], 'name'));
      $rows[] = [
        $r['id'],
        $r['title'],
        $r['start_date'],
        $r['score'],
        $r['match_reason'],
        $topics ?: '—',
      ];
    }

    $this->io()->table(['ID', 'Title', 'Start', 'Score', 'Reason', 'Topics'], $rows);
  }

  /**
   * Find topic term IDs by partial name.
   */
  protected function findTopicIdsByName(string $name): array {
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $tids = $storage->getQuery()
      ->condition('vid', 'tags')
      ->condition('name', '%' . $name . '%', 'LIKE')
      ->accessCheck(FALSE)
      ->execute();
    return array_map('intval', array_values($tids));
  }

}
