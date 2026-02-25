<?php

declare(strict_types=1);

namespace Drupal\prod_hardener\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

final class ProdHardenerSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames(): array {
    return ['prod_hardener.settings'];
  }

  public function getFormId(): string {
    return 'prod_hardener_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('prod_hardener.settings');

    $form['intro'] = [
      '#markup' => $this->t('<p>List development modules (one per line) to auto-disable on <strong>production</strong> environments. Set <code>DRUPAL_ENV=prod</code> or <code>$settings["prod_hardener_env"]="prod";</code>.</p>'),
    ];

    $form['dev_modules'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Dev modules to disable on prod'),
      '#default_value' => $config->get('dev_modules') ?? "devel\nviews_ui\ntour",
      '#description' => $this->t('One machine name per line.'),
      '#rows' => 6,
    ];

    $form['note'] = [
      '#markup' => $this->t('<p><strong>Tip:</strong> Also ensure CSS/JS aggregation and Twig caches are enabled in production.</p>'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->configFactory->getEditable('prod_hardener.settings')
      ->set('dev_modules', $form_state->getValue('dev_modules'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}