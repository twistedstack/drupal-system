
<?php

namespace Drupal\author_override_default\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

class AuthorOverrideDefaultSettingsForm extends ConfigFormBase {

  public function getFormId() {
    return 'author_override_default_settings_form';
  }

  protected function getEditableConfigNames() {
    return ['author_override_default.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('author_override_default.settings');

    $form['field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User reference field machine name'),
      '#default_value' => $config->get('field_name') ?? 'field_author_override',
      '#required' => TRUE,
      '#description' => $this->t('Example: field_author_override. Must be a user reference field.'),
    ];

    $bundles = NodeType::loadMultiple();
    $options = [];
    foreach ($bundles as $id => $type) {
      $options[$id] = $type->label();
    }

    $form['bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Apply to these content types'),
      '#options' => $options,
      '#default_value' => $config->get('bundles') ?? [],
      '#description' => $this->t('Leave all unchecked to apply to all content types.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $bundles = array_filter($form_state->getValue('bundles') ?? []);
    $this->config('author_override_default.settings')
      ->set('field_name', $form_state->getValue('field_name'))
      ->set('bundles', $bundles)
      ->save();
  }
}
