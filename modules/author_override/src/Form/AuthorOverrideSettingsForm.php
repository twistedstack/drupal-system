<?php

declare(strict_types=1);

namespace Drupal\author_override\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for Author Override.
 */
final class AuthorOverrideSettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'author_override_settings_form';
  }

  protected function getEditableConfigNames(): array {
    return ['author_override.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('author_override.settings');

    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');
    $options = [];
    foreach ($bundles as $machine => $info) {
      $options[$machine] = $info['label'] ?? $machine;
    }

    $form['allowed_bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allowed bundles'),
      '#description' => $this->t('Features apply only to these node types. If none selected, nothing applies.'),
      '#options' => $options,
      '#default_value' => $config->get('allowed_bundles') ?: [],
    ];

    $fields = [
      'autofill_on_create' => 'Auto-fill on creation',
      'autofill_on_edit' => 'Auto-fill on edit',
      'sync_on_author_change' => 'Sync override if owner changes',
      'show_use_original_checkbox' => 'Show "Use original author" checkbox',
      'apply_override_changes_owner' => 'Apply override by changing node owner on save',
      'clear_override_after_apply' => 'Clear override after apply',
      'update_created_time_on_apply' => 'Update created time on apply',
      'hide_core_authoring_for_non_admin' => 'Hide core "Authored by" controls for non-admin users',
    ];

    foreach ($fields as $key => $label) {
      $form[$key] = [
        '#type' => 'checkbox',
        '#title' => $this->t($label),
        '#default_value' => (bool) $config->get($key),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $allowed = array_values(array_filter($form_state->getValue('allowed_bundles') ?? []));
    $edit = $this->configFactory()->getEditable('author_override.settings');
    $edit->set('allowed_bundles', $allowed);

    $fields = [
      'autofill_on_create', 'autofill_on_edit', 'sync_on_author_change',
      'show_use_original_checkbox', 'apply_override_changes_owner',
      'clear_override_after_apply', 'update_created_time_on_apply',
      'hide_core_authoring_for_non_admin'
    ];

    foreach ($fields as $f) {
      $edit->set($f, (bool) $form_state->getValue($f));
    }

    $edit->save();
    parent::submitForm($form, $form_state);
  }

}
