<?php

namespace Drupal\bulk_taxonomy_update\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\workflows\Entity\Workflow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to bulk update taxonomy reference fields on nodes.
 */
class BulkTermUpdateForm extends FormBase {

  protected EntityTypeBundleInfoInterface $bundleInfo;
  protected EntityFieldManagerInterface $entityFieldManager;

  public static function create(ContainerInterface $container) {
    $instance = new static();
    $instance->bundleInfo = $container->get('entity_type.bundle.info');
    $instance->entityFieldManager = $container->get('entity_field.manager');
    return $instance;
  }

  public function getFormId() {
    return 'bulk_taxonomy_update_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $bundles = $this->bundleInfo->getBundleInfo('node');
    $options = [];
    foreach ($bundles as $machine => $info) {
      $options[$machine] = $info['label'] ?? $machine;
    }

    $selected_bundle = $form_state->getValue('bundle') ?: array_key_first($options);

    $form['description'] = [
      '#markup' => '<p>Bulk set/append/remove taxonomy term(s) on nodes. Use optional filters to limit the affected content.</p>',
    ];

    $form['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#options' => $options,
      '#default_value' => $selected_bundle,
      '#ajax' => [
        'callback' => '::refreshFields',
        'wrapper' => 'field-wrapper',
      ],
      '#required' => TRUE,
    ];

    // Field selectors (target field + optional section field for filtering).
    $field_options = $this->getTaxonomyFieldOptions($selected_bundle);
    $form['field_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'field-wrapper'],
    ];
    $form['field_wrapper']['field_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Taxonomy reference field to update'),
      '#options' => $field_options,
      '#required' => TRUE,
    ];
    $form['field_wrapper']['section_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Section field (for filter)'),
      '#options' => ['' => $this->t('- None -')] + $field_options,
      '#default_value' => '',
      '#description' => $this->t('Choose the taxonomy reference field used by Workbench Section (if applicable).'),
    ];

    $form['term_ids'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Term(s) to apply'),
      '#target_type' => 'taxonomy_term',
      '#tags' => TRUE,
      '#required' => TRUE,
      '#description' => $this->t('Choose one or more taxonomy terms.'),
    ];

    $form['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Operation'),
      '#options' => [
        'set' => $this->t('Set (replace existing values)'),
        'append' => $this->t('Append (add to existing values)'),
        'remove' => $this->t('Remove (delete these values if present)'),
      ],
      '#default_value' => 'set',
      '#required' => TRUE,
    ];

    // Filters.
    $form['filters'] = [
      '#type' => 'details',
      '#title' => $this->t('Filters'),
      '#open' => TRUE,
    ];

    $form['filters']['moderation_state'] = [
      '#type' => 'select',
      '#title' => $this->t('Moderation state'),
      '#options' => $this->getModerationStates($selected_bundle),
      '#empty_option' => $this->t('- Any -'),
    ];

    $form['filters']['author'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Author'),
      '#target_type' => 'user',
      '#description' => $this->t('Limit to nodes authored by this user.'),
    ];

    $form['filters']['section_tid'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Workbench section term'),
      '#target_type' => 'taxonomy_term',
      '#description' => $this->t('If you selected a Section field above, choose the specific term to filter by.'),
    ];

    $form['dry_run'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Dry run (log only, don\'t save)'),
      '#default_value' => FALSE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run batch update'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * AJAX callback to refresh field selects when bundle changes.
   */
  public function refreshFields(array &$form, FormStateInterface $form_state) {
    $bundle = $form_state->getValue('bundle');
    $options = $this->getTaxonomyFieldOptions($bundle);
    $form['field_wrapper']['field_name']['#options'] = $options;
    $form['field_wrapper']['section_field']['#options'] = ['' => $this->t('- None -')] + $options;
    // Update moderation state options.
    $form['filters']['moderation_state']['#options'] = $this->getModerationStates($bundle);
    return $form['field_wrapper'];
  }

  /**
   * Get taxonomy_reference field options for a bundle.
   */
  protected function getTaxonomyFieldOptions(string $bundle): array {
    $options = [];
    if (!$bundle) {
      return $options;
    }
    $defs = $this->entityFieldManager->getFieldDefinitions('node', $bundle);
    foreach ($defs as $name => $def) {
      if ($def->getType() === 'entity_reference' && ($def->getSetting('target_type') === 'taxonomy_term')) {
        $options[$name] = $def->getLabel() . ' (' . $name . ')';
      }
    }
    asort($options);
    return $options;
  }

  /**
   * Get moderation state options for a bundle.
   */
  protected function getModerationStates(string $bundle): array {
    $options = [];
    // Find content_moderation workflows and list states for ones that apply to bundle.
    $workflows = Workflow::loadMultiple();
    foreach ($workflows as $workflow) {
      /** @var \Drupal\workflows\Entity\Workflow $workflow */
      if ($workflow->getTypePluginId() !== 'content_moderation') {
        continue;
      }
      $type_settings = $workflow->get('type_settings');
      $bundles = $type_settings['entity_types']['node'] ?? [];
      if (!in_array($bundle, $bundles, TRUE)) {
        continue;
      }
      $states = $workflow->getTypePlugin()->getStates();
      foreach ($states as $state_id => $state) {
        $options[$state_id] = $state->label();
      }
    }
    asort($options);
    return $options;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $tids = [];
    foreach ((array) $form_state->getValue('term_ids') as $item) {
      if (is_array($item) && isset($item['target_id'])) {
        $tids[] = (int) $item['target_id'];
      }
    }
    if (empty($tids)) {
      $form_state->setErrorByName('term_ids', $this->t('Please select at least one term.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $bundle = $form_state->getValue('bundle');
    $field = $form_state->getValue('field_name');
    $mode = $form_state->getValue('mode');
    $dry_run = (bool) $form_state->getValue('dry_run');

    $author_uid = NULL;
    $author_val = $form_state->getValue('author');
    if (is_array($author_val) && isset($author_val['target_id']) && $author_val['target_id']) {
      $author_uid = (int) $author_val['target_id'];
    }

    $moderation_state = $form_state->getValue('moderation_state') ?: NULL;

    $section_field = $form_state->getValue('section_field') ?: NULL;
    $section_tid = NULL;
    $section_val = $form_state->getValue('section_tid');
    if (is_array($section_val) && isset($section_val['target_id']) && $section_val['target_id']) {
      $section_tid = (int) $section_val['target_id'];
    }

    $tids = [];
    foreach ((array) $form_state->getValue('term_ids') as $item) {
      if (is_array($item) && isset($item['target_id'])) {
        $tids[] = (int) $item['target_id'];
      }
    }

    // Build NID list & batch operations with filters.
    $nids = \Drupal::service('bulk_taxonomy_update.updater')
      ->loadNodeIdsForBundle($bundle, $moderation_state, $author_uid, $section_field, $section_tid);

    $chunks = array_chunk($nids, 50);

    $builder = new BatchBuilder();
    $builder->setTitle($this->t('Bulk taxonomy update'))
      ->setInitMessage($this->t('Preparing to update nodes...'))
      ->setProgressMessage($this->t('Processed @current out of @total batches.'))
      ->setErrorMessage($this->t('Bulk update encountered an error.'));

    foreach ($chunks as $chunk) {
      $builder->addOperation([static::class, 'batchProcess'], [
        $chunk,
        $field,
        $tids,
        $mode,
        $dry_run,
      ]);
    }

    $builder->setFinishCallback([static::class, 'batchFinished']);
    batch_set($builder->toArray());

    $this->messenger()->addStatus($this->t('Batch started for @count nodes.', ['@count' => count($nids)]));
  }

  /**
   * Batch operation callback.
   */
  public static function batchProcess(array $nids, string $field, array $tids, string $mode, bool $dry_run, array &$context) {
    /** @var \Drupal\bulk_taxonomy_update\Service\BulkUpdater $updater */
    $updater = \Drupal::service('bulk_taxonomy_update.updater');
    $updater->process($nids, $field, $tids, $mode, $dry_run, $context);
  }

  /**
   * Batch finished callback.
   */
  public static function batchFinished(bool $success, array $results, array $operations) {
    $messenger = \Drupal::messenger();
    if ($success) {
      $updated = $results['updated'] ?? 0;
      $messenger->addStatus(t('Bulk taxonomy update completed. Nodes updated: @count', ['@count' => $updated]));
    }
    else {
      $messenger->addError(t('Bulk taxonomy update failed. Some operations did not complete.'));
    }
  }

}
