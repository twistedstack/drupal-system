<?php

declare(strict_types=1);

namespace Drupal\author_override;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

final class AuthorResolver {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public function getEffectiveAuthor(NodeInterface $node): UserInterface {
    if ($node->hasField('field_author_override') && !$node->get('field_author_override')->isEmpty()) {
      $target = $node->get('field_author_override')->entity;
      if ($target instanceof UserInterface) {
        return $target;
      }
    }
    return $node->getOwner();
  }

}
