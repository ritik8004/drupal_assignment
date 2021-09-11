<?php

/**
 * @file
 * Hooks specific to the alshaya_rcs module.
 */

/**
 * Implements hook_rcs_twig_templates().
 */
function hook_rcs_twig_templates() {
  // List of twig templates to be embedded on the page.
  // You need to load the contents of the twig file and store in the array.
  return [
    // List of block templates.
    'block' => [],
    // List of field templates.
    'field' => [
      // Entity name.
      'page' => [
        // Field Name and path.
        'title' => file_get_contents("/path/template.html.twig"),
      ],
    ],
  ];
}

/**
 * Implements hook_rcs_twig_templates_alter().
 */
function hook_rcs_twig_templates_alter(&$templates, $entity) {
  if (!$entity || $entity->getType() !== 'page') {
    return;
  }

  // Alter list of twig templates.
  $templates['field']['page']['description'] = '<div>{{ description }}</div>';
}
