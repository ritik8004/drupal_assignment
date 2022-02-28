<?php

/**
 * @file
 * Consist of post update changes.
 */

/**
 * Fix issue with solr field type.
 */
function alshaya_post_update_8014() {
  // Apply entity update as it is not working through search api solr.
  $change_list = \Drupal::entityDefinitionUpdateManager()->getChangeList();
  if (isset($change_list['solr_field_type'])) {
    $entity_type_Manager = \Drupal::entityTypeManager();
    $entity_type_Manager->clearCachedDefinitions();
    $entity_type = $entity_type_Manager->getDefinition('solr_field_type');
    \Drupal::service('entity_type.listener')->onEntityTypeCreate($entity_type);
  }
}
