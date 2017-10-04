<?php

/**
 * @file
 * Lists available colors and color schemes for the Alshaya White Label theme.
 */

$info = [
  'fields' => [
    'text' => t('Text color'),
    'link' => t('Link color'),
    'primary' => t('Primary color'),
    'secondary' => t('Secondary color'),
    'tertiary' => t('Tertiary color'),
    'delivery' => t('Delivery Menu'),
  ],
  'schemes' => [
    'default' => [
      'title' => t('Alshaya White Label (default)'),
      'colors' => [
        'text' => '#666666',
        'link' => '#0060aa',
        'primary' => '#0060aa',
        'secondary' => '#d9efff',
        'tertiary' => '#ff6d22',
        'delivery' => '#6aade4',
      ],
    ],
  ],
  'css' => [
    'css/colors.css',
  ],
];
