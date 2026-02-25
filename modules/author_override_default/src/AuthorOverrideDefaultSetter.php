
<?php

namespace Drupal\author_override_default;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

class AuthorOverrideDefaultSetter {

  protected $currentUser;
  protected $entityTypeManager;

  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  public function appliesToBundle(string $bundle, array $enabled_bundles): bool {
    if (empty($enabled_bundles)) {
      return TRUE;
    }
    return in_array($bundle, $enabled_bundles, TRUE);
  }

  public function applyPresave(NodeInterface $node, string $field_name): void {
    if (!$node->hasField($field_name)) {
      return;
    }
    $field = $node->get($field_name);
    if ($field->isEmpty()) {
      $uid = $this->currentUser->id();
      if ($uid) {
        $node->set($field_name, ['target_id' => $uid]);
      }
    }
  }

  public function applyFormDefault(array &$form, string $field_name): void {
    if (!isset($form[$field_name]['widget'][0]['target_id'])) {
      return;
    }
    $element =& $form[$field_name]['widget'][0]['target_id'];
    if (!empty($element['#default_value'])) {
      return;
    }
    $uid = $this->currentUser->id();
    if ($uid) {
      $element['#default_value'] = $uid;
    }
  }
}
