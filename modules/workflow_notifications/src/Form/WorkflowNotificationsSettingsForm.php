<?php

namespace Drupal\workflow_notifications\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;

/**
 * Configure Workflow Notifications settings.
 */
class WorkflowNotificationsSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['workflow_notifications.settings'];
  }

  public function getFormId() {
    return 'workflow_notifications_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('workflow_notifications.settings');

    $roles = Role::loadMultiple();
    $role_options = [];
    foreach ($roles as $role) {
      if (!in_array($role->id(), ['anonymous', 'authenticated'])) {
        $role_options[$role->id()] = $role->label();
      }
    }

    $form['recipients_roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Notify roles'),
      '#description' => $this->t('Users with these roles will receive notifications when content is published or archived.'),
      '#options' => $role_options,
      '#default_value' => $config->get('recipients_roles') ?: [],
    ];

    $form['recipients_static'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Additional email addresses'),
      '#description' => $this->t('Comma-separated list of emails to also notify.'),
      '#default_value' => $config->get('recipients_static'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('workflow_notifications.settings')
      ->set('recipients_roles', $form_state->getValue('recipients_roles'))
      ->set('recipients_static', $form_state->getValue('recipients_static'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
