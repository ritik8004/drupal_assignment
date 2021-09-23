<?php

/**
 * @file
 * Hooks specific to the rcs_handlebars module.
 */

/**
 * Implements hook_rcs_handlebars_templates().
 */
function hook_rcs_handlebars_templates() {
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
 * Implements hook_rcs_handlebars_templates_alter().
 */
function hook_rcs_handlebars_templates_alter(&$templates, $entity) {
  if (!$entity || $entity->bundle() !== 'page') {
    return;
  }

  // Alter list of handlebars templates.
  $templates['field']['page']['description'] = '/path/description.handlebars';
}
