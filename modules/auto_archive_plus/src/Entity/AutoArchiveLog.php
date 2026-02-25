<?php

namespace Drupal\auto_archive_plus\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * @ContentEntityType(
 *   id = "auto_archive_log",
 *   label = @Translation("Auto Archive Log"),
 *   base_table = "auto_archive_log",
 *   admin_permission = "administer auto archive log",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title"
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\\auto_archive_plus\\AutoArchiveLogListBuilder"
 *   }
 * )
 */
class AutoArchiveLog extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['node_id'] = BaseFieldDefinition::create('integer')->setLabel(t('Node ID'));
    $fields['title'] = BaseFieldDefinition::create('string')->setLabel(t('Title'));
    $fields['type'] = BaseFieldDefinition::create('string')->setLabel(t('Content type'));
    $fields['archived'] = BaseFieldDefinition::create('datetime')->setLabel(t('Archived date'));

    return $fields;
  }
}
