<?php

namespace Drupal\bulk_taxonomy_update\Commands;

use Drupal\bulk_taxonomy_update\Service\BulkUpdater;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for Bulk Taxonomy Update.
 */
class BulkTaxonomyUpdateCommands extends DrushCommands {

  protected BulkUpdater $updater;

  public function __construct(BulkUpdater $updater) {
    parent::__construct();
    $this->updater = $updater;
  }

  /**
   * Apply taxonomy updates to nodes.
   *
   * @command bulk-taxonomy-update:apply
   * @aliases btua
   * @option type The node bundle machine name (e.g., article).
   * @option field The taxonomy reference field machine name (e.g., field_topic).
   * @option tids Comma-separated term IDs (e.g., 12,34,56).
   * @option mode Operation mode: set, append, or remove.
   * @option dry-run If set, do not save changes.
   * @option moderation-state Moderation state to filter by (e.g., draft, published).
   * @option author Author UID to filter by.
   * @option section-field Machine name of section taxonomy reference field to filter by (e.g., field_section).
   * @option section-tid Section term ID to filter by.
   * @usage drush bulk-taxonomy-update:apply --type=article --field=field_topic --tids=45 --mode=set
   * @usage drush btua --type=article --field=field_topic --tids=45,67 --mode=append --moderation-state=published
   * @usage drush btua --type=article --field=field_topic --tids=45 --mode=set --author=3
   * @usage drush btua --type=article --field=field_topic --tids=12 --mode=append --section-field=field_section --section-tid=99
   */
  public function apply(array $args = [], array $options = [
    'type' => '',
    'field' => '',
    'tids' => '',
    'mode' => 'set',
    'dry-run' => FALSE,
    'moderation-state' => '',
    'author' => '',
    'section-field' => '',
    'section-tid' => '',
  ]) : int {
    $bundle = (string) ($options['type'] ?? '');
    $field = (string) ($options['field'] ?? '');
    $mode = (string) ($options['mode'] ?? 'set');
    $dry = (bool) ($options['dry-run'] ?? FALSE);

    if (!$bundle || !$field) {
      $this->logger()->error('Both --type and --field are required.');
      return self::EXIT_FAILURE;
    }

    $tids = [];
    if (!empty($options['tids'])) {
      foreach (explode(',', (string) $options['tids']) as $tid) {
        $tid = (int) trim($tid);
        if ($tid) {
          $tids[] = $tid;
        }
      }
    }
    if (empty($tids) && $mode !== 'remove') {
      $this->logger()->error('Provide --tids for modes other than remove.');
      return self::EXIT_FAILURE;
    }

    $moderation = $options['moderation-state'] ?: NULL;
    $author = $options['author'] !== '' ? (int) $options['author'] : NULL;
    $section_field = $options['section-field'] ?: NULL;
    $section_tid = $options['section-tid'] !== '' ? (int) $options['section-tid'] : NULL;

    $nids = $this->updater->loadNodeIdsForBundle($bundle, $moderation, $author, $section_field, $section_tid);
    $this->logger()->notice(sprintf('Found %d nodes in bundle %s.', count($nids), $bundle));

    $chunks = array_chunk($nids, 100);
    $context = ['results' => ['updated' => 0]];
    foreach ($chunks as $chunk) {
      $this->updater->process($chunk, $field, $tids, $mode, $dry, $context);
    }

    $this->logger()->success(sprintf('Done. Nodes updated: %d. Dry run: %s', $context['results']['updated'], $dry ? 'yes' : 'no'));
    return self::EXIT_SUCCESS;
  }

}
