<?php

/**
 * @file
 * Lists available colors and color schemes for the Alshaya White Label theme.
 */

$info = array(
  'fields' => array(
    'text' => t('Text color'),
    'link' => t('Link color'),
    'primary' => t('Primary color'),
    'secondary' => t('Secondary color'),
    'tertiary' => t('Tertiary color'),
  ),
  'schemes' => array(
    'default' => array(
      'title' => t('Alshaya White Label (default)'),
      'colors' => array(
        'text' => '#3b3b3b',
        'link' => '#0060aa',
        'primary' => '#0060aa',
        'secondary' => '#d9efff',
        'tertiary' => '#ff6d22',
      ),
    ),
  ),
  'css' => array(
    'css/colors.css',
  ),
);
