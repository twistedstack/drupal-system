<?php

namespace Drupal\auto_archive_plus;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for Auto Archive Log entities.
 */
class AutoArchiveLogListBuilder extends EntityListBuilder {

  public function buildHeader(): array {
    $header['id'] = $this->t('ID');
    $header['node_id'] = $this->t('Node ID');
    $header['title'] = $this->t('Title');
    $header['type'] = $this->t('Content Type');
    $header['archived'] = $this->t('Archived Date');
    return $header + parent::buildHeader();
  }

  public function buildRow(EntityInterface $entity): array {
    $row['id'] = $entity->id();
    $row['node_id'] = $entity->get('node_id')->value;
    $row['title'] = $entity->get('title')->value;
    $row['type'] = $entity->get('type')->value;
    $row['archived'] = $entity->get('archived')->value;
    return $row + parent::buildRow($entity);
  }
}
