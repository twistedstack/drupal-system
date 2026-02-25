<?php

namespace Drupal\archive_request\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides an 'Archive Request' Block.
 *
 * @Block(
 *   id = "archive_request_block",
 *   admin_label = @Translation("Archive Request Block")
 * )
 */
class ArchiveRequestBlock extends BlockBase {

  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface && $node->hasField('field_archive_request') && !$node->get('field_archive_request')->value) {
      $url = Url::fromRoute('archive_request.flag', ['node' => $node->id()]);
      return [
        'link' => Link::fromTextAndUrl($this->t('Request Archive'), $url)->toRenderable(),
      ];
    }
    return [];
  }

  public function access(AccountInterface $account) {
    $node = \Drupal::routeMatch()->getParameter('node');
    return AccessResult::allowedIf(
      $node instanceof NodeInterface &&
      $account->hasPermission('request archive content')
    );
  }

}