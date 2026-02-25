<?php

namespace Drupal\auto_archive_plus\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldManagerInterface;
use Drupal\auto_archive_plus\Service\AutoArchiveService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AutoArchiveSettingsForm extends ConfigFormBase {

  protected EntityTypeManagerInterface $entityTypeManager;
  protected FieldManagerInterface $fieldManager;
  protected AutoArchiveService $autoArchiveService;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, FieldManagerInterface $field_manager, AutoArchiveService $auto_archive_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fieldManager = $field_manager;
    $this->autoArchiveService = $auto_archive_service;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('auto_archive_plus.service')
    );
  }

  public function getFormId(): string {
    return 'auto_archive_plus_settings_form';
  }

  protected function getEditableConfigNames(): array {
    return ['auto_archive_plus.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('auto_archive_plus.settings');
    $types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();

    $options = [];
    foreach ($types as $type) {
      $options[$type->id()] = $type->label();
    }

    $form['enabled_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable auto-archive for content types'),
      '#options' => $options,
      '#default_value' => $config->get('enabled_content_types') ?? [],
    ];

    $form['content_types'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Content type'),
        $this->t('Archive after (days)'),
        $this->t('Date field'),
      ],
    ];

    foreach ($types as $type) {
      $id = $type->id();
      $form['content_types'][$id]['label'] = ['#markup' => $type->label()];
      $form['content_types'][$id]['days'] = ['#type' => 'number', '#min' => 1, '#default_value' => $config->get("content_types.$id.days") ?? 90];
      $form['content_types'][$id]['date_field'] = [
        '#type' => 'select',
        '#options' => $this->getDateFields($id),
        '#default_value' => $config->get("content_types.$id.date_field") ?? '',
      ];
    }

    if ($this->currentUser()->hasPermission('run auto archive plus')) {
      $form['run_now'] = [
        '#type' => 'submit',
        '#value' => $this->t('Run Auto Archive Now'),
        '#submit' => ['::runNow'],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  protected function getDateFields(string $bundle): array {
    $fields = $this->fieldManager->getFieldDefinitions('node', $bundle);

    $options = ['' => $this->t('- Use created date -')];
    foreach ($fields as $name => $definition) {
      if (in_array($definition->getType(), ['date', 'datetime'])) {
        $options[$name] = $definition->getLabel();
      }
    }

    return $options;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $enabled = array_filter($form_state->getValue('enabled_content_types'));
    $settings = array_intersect_key($form_state->getValue('content_types'), $enabled);

    $this->config('auto_archive_plus.settings')
      ->set('enabled_content_types', $enabled)
      ->set('content_types', $settings)
      ->save();

    parent::submitForm($form, $form_state);
  }

  public function runNow(array &$form, FormStateInterface $form_state): void {
    $batch = [
      'title' => $this->t('Auto Archiving nodes...'),
      'operations' => $this->autoArchiveService->getBatchOperations(),
      'finished' => [AutoArchiveService::class, 'batchFinished'],
    ];
    batch_set($batch);
  }
}
