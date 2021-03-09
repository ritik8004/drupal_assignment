<?php

namespace Drupal\alshaya_algolia_react\Services;

/**
 * Helper service for algolia react.
 */
class AlshayaAlgoliaReactHelper {

  /**
   * Format / Clean the rule context string.
   *
   * @param string $context
   *   Rule context string.
   *
   * @return string|string[]
   *   Formatted or cleaned rule context.
   */
  public function formatCleanRuleContext(string $context) {
    $context = strtolower(trim($context));
    // Remove special characters.
    $context = preg_replace("/[^a-zA-Z0-9\s]/", "", $context);
    // Ensure duplicate spaces are replaced with single space.
    // H & M would have become H  M after preg_replace.
    $context = str_replace('  ', ' ', $context);

    // Replace spaces with underscore.
    $context = str_replace(' ', '_', $context);

    return $context;
  }

}
