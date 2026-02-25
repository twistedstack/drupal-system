<?php

namespace Drupal\custom_moderation_action\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Sets the moderation state to Draft.
 *
 * @Action(
 *   id = "set_moderation_state_draft",
 *   label = @Translation("Set Moderation State to Draft"),
 *   type = "node"
 * )
 */
class SetModerationStateDraft extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(EntityInterface $entity = NULL) {
    // Check that the entity has a moderation_state field.
    if ($entity && $entity->hasField('moderation_state')) {
      $entity->set('moderation_state', 'draft');
      $entity->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->get('moderation_state')->access('edit', $account, TRUE));
    return $return_as_object ? $result : $result->isAllowed();
  }
}