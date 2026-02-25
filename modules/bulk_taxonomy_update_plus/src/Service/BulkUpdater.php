<?php

namespace Drupal\bulk_taxonomy_update\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\node\NodeInterface;

/**
 * Service that performs bulk updates on taxonomy reference fields.
 */
class BulkUpdater {

  protected EntityTypeManagerInterface $entityTypeManager;
  protected LoggerChannelInterface $logger;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelInterface $logger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
  }

  /**
   * Loads IDs of nodes for a bundle with optional filters.
   *
   * @param string $bundle
   * @param string|null $moderation_state
   * @param int|null $author_uid
   * @param string|null $section_field  Machine name of taxonomy term reference field to filter by.
   * @param int|null $section_tid
   */
  public function loadNodeIdsForBundle(string $bundle, ?string $moderation_state = NULL, ?int $author_uid = NULL, ?string $section_field = NULL, ?int $section_tid = NULL): array {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', $bundle)
      ->accessCheck(TRUE);

    if (!empty($moderation_state)) {
      $query->condition('moderation_state', $moderation_state);
    }
    if (!empty($author_uid)) {
      $query->condition('uid', $author_uid);
    }
    if (!empty($section_field) && !empty($section_tid)) {
      // Add condition on the chosen section field.
      $query->condition($section_field . '.target_id', $section_tid);
    }

    $nids = $query->execute();
    return array_values($nids);
  }

  /**
   * Process a chunk of node IDs.
   *
   * @param array $nids
   * @param string $field
   * @param array $target_tids
   * @param string $mode 'set'|'append'|'remove'
   * @param bool $dry_run
   * @param array &$context
   */
  public function process(array $nids, string $field, array $target_tids, string $mode = 'set', bool $dry_run = FALSE, array &$context = []) : void {
    $storage = $this->entityTypeManager->getStorage('node');
    $nodes = $storage->loadMultiple($nids);

    $updated = 0;
    foreach ($nodes as $node) {
      if (!$node instanceof NodeInterface) {
        continue;
      }
      if (!$node->hasField($field)) {
        continue;
      }

      $current = array_map(fn($item) => (int) $item['target_id'], $node->get($field)->getValue());

      $new = $current;
      if ($mode === 'set') {
        $new = $target_tids;
      }
      elseif ($mode === 'append') {
        $new = array_values(array_unique(array_merge($current, $target_tids)));
      }
      elseif ($mode === 'remove') {
        $new = array_values(array_diff($current, $target_tids));
      }

      if ($new === $current) {
        continue;
      }

      if (!$dry_run) {
        $node->set($field, $new);
        $node->save();
      }
      $updated++;
      $this->logger->notice('Node @nid updated for field @field (mode=@mode).', [
        '@nid' => $node->id(),
        '@field' => $field,
        '@mode' => $mode,
      ]);
    }

    if (!isset($context['results'])) {
      $context['results'] = ['updated' => 0];
    }
    $context['results']['updated'] += $updated;
  }

}
