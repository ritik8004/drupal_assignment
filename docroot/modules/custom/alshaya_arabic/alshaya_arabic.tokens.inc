<?php

/**
 * @file
 * Token integration for the Alshaya Arabic module.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function alshaya_arabic_token_info() {
  $info = [];

  $info['tokens']['array']['reverse-join-path'] = [
    'name' => t('Joined path in reverse'),
    'description' => t('The array values each cleaned by Pathauto and then joined in reverse order with the slash into a string that resembles an URL.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_arabic_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'array' && !empty($data['array'])) {
    $array = $data['array'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'reverse-join-path':
          $values = [];
          foreach (token_element_children($array) as $key) {
            $value = is_array($array[$key]) ? render($array[$key]) : (string) $array[$key];
            $value = \Drupal::service('pathauto.alias_cleaner')->cleanString($value, $options);
            $values[] = $value;
          }
          $reversed_values = array_reverse($values);
          $replacements[$original] = implode('/', $reversed_values);
          break;
      }
    }
  }

  return $replacements;
}
