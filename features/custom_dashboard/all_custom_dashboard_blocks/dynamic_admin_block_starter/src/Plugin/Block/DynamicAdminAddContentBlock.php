<?php

namespace Drupal\dynamic_admin_block_starter\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a configurable dynamic content block.
 *
 * @Block(
 *   id = "dynamicadminaddcontentblock",
 *   admin_label = @Translation("Dynamicadminaddcontent"),
 *   category = @Translation("Custom")
 * )
 */
class DynamicAdminAddContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $account;
  protected $entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $account, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  public function defaultConfiguration() {
    return [
      'content_types' => [],
      'button_label' => '+ Add Content',
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $bundles = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $options = [];
    foreach ($bundles as $id => $bundle) {
      $options[$id] = $bundle->label();
    }

    $form['content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types to include'),
      '#description' => $this->t('Select which content types to show if the user has permission to create them.'),
      '#options' => $options,
      '#default_value' => $this->configuration['content_types'],
    ];

    $form['button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dropdown label'),
      '#default_value' => $this->configuration['button_label'],
      '#size' => 30,
      '#maxlength' => 100,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['content_types'] = array_filter($form_state->getValue('content_types'));
    $this->configuration['button_label'] = $form_state->getValue('button_label');
  }

  public function build() {
    $bundles = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $selected = $this->configuration['content_types'];
    $label = $this->configuration['button_label'];
    $links = [];

    foreach ($bundles as $id => $bundle) {
      if (!empty($selected) && !in_array($id, $selected)) {
        continue;
      }

      $permission = "create $id content";
      if ($this->account->hasPermission($permission)) {
        $links[] = Link::fromTextAndUrl(
          $this->t('Add @label', ['@label' => $bundle->label()]),
          Url::fromRoute('node.add', ['node_type' => $id])
        )->toRenderable();
      }
    }

    if (empty($links)) {
      return [];
    }

    return [
      '#theme' => 'item_list',
      '#title' => $label,
      '#items' => $links,
      '#attributes' => ['class' => ['admin-dashboard-block']],
      '#cache' => ['contexts' => ['user.permissions']],
    ];
  }

}
