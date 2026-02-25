<?php

namespace Drupal\tool_settings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ToolSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['tool_settings.settings'];
  }

  public function getFormId() {
    return 'tool_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tool_settings.settings');

    $form['tool_display_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Tool Display Mode'),
      '#options' => [
        'full' => $this->t('Full'),
        'teaser' => $this->t('Teaser'),
      ],
      '#default_value' => $config->get('tool_display_mode') ?? 'full',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('tool_settings.settings')
      ->set('tool_display_mode', $form_state->getValue('tool_display_mode'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
