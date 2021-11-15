<?php

/**
 * @file
 * Hooks specific to the rcs_handlebars module.
 */

/**
 * Allows modules to define their own handlebars templates.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity being processed.
 */
function hook_rcs_handlebars_templates(\Drupal\Core\Entity\EntityInterface $entity) {
  // List of handlebars templates to be embedded on the page.
  return [
    // List of block templates.
    'block' => [],
    // List of field templates.
    'field' => [
      // Entity name.
      'page' => [
        // Field Name and path.
        'title' => "/path/template.handlebars",
      ],
    ],
  ];
}

/**
 * Allows module to alter the handlebars templates added by other modules.
 *
 * @param array $templates
 *   The array of templates to alter.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity being processed.
 */
function hook_rcs_handlebars_templates_alter(array &$templates, \Drupal\Core\Entity\EntityInterface $entity) {
  if (!$entity || $entity->bundle() !== 'page') {
    return;
  }

  // Alter list of handlebars templates.
  $templates['field']['page']['description'] = '/path/description.handlebars';
}
