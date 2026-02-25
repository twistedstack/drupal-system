<?php

namespace Drupal\archive_request\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\Entity\Node;

/**
 * Handles archive request flagging.
 */
class ArchiveRequestController extends ControllerBase {

  public function requestArchive($node) {
    $node = Node::load($node);
    if ($node && $node->access('update') && $this->currentUser()->hasPermission('request archive content')) {
      if ($node->hasField('field_archive_request')) {
        $node->set('field_archive_request', TRUE);
        $node->save();
        $this->messenger()->addStatus($this->t('Archive request has been flagged.'));
      }
      else {
        $this->messenger()->addError($this->t('This content type does not support archive requests.'));
      }
    }
    return new RedirectResponse(\Drupal::request()->headers->get('referer') ?: '/');
  }

}