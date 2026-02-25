<?php

namespace Drupal\auto_archive_plus\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AutoArchiveSettingsForm extends ConfigFormBase {

  protected EntityFieldManagerInterface $fieldManager;

  public function __construct(EntityFieldManagerInterface $field_manager) {
    $this->fieldManager = $field_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  protected function getEditableConfigNames() {
    return ['auto_archive_plus.settings'];
  }

  public function getFormId() {
    return 'auto_archive_plus_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('auto_archive_plus.settings');

    $content_types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();

    foreach ($content_types as $type_id => $type) {
      $fields = $this->fieldManager->getFieldDefinitions('node', $type_id);

      $date_fields = [];
      foreach ($fields as $field_name => $field) {
        if ($field->getType() === 'timestamp') {
          $date_fields[$field_name] = $field->getLabel();
        }
      }

      $form['content_types'][$type_id] = [
        '#type' => 'details',
        '#title' => $type->label(),
        '#open' => FALSE,
      ];

      $form['content_types'][$type_id]['enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable auto-archive'),
        '#default_value' => $config->get("content_types.$type_id.enabled"),
      ];

      $form['content_types'][$type_id]['days'] = [
        '#type' => 'number',
        '#title' => $this->t('Archive after X days'),
        '#default_value' => $config->get("content_types.$type_id.days") ?? 90,
      ];

      $form['content_types'][$type_id]['date_field'] = [
        '#type' => 'select',
        '#title' => $this->t('Date field (optional)'),
        '#options' => ['' => $this->t('- Use created date -')] + $date_fields,
        '#default_value' => $config->get("content_types.$type_id.date_field"),
      ];
    }

    // 🔘 Manual run button
    $form['run_now'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run Auto Archive Now'),
      '#submit' => ['::runNow'],
      '#button_type' => 'secondary',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('content_types');
    $this->config('auto_archive_plus.settings')
      ->set('content_types', $values)
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Manual execution handler.
   */
  public function runNow(array &$form, FormStateInterface $form_state) {
    \Drupal::service('auto_archive_plus.service')->processAutoArchive();
    $this->messenger()->addStatus($this->t('Auto Archive process completed.'));
  }
}
