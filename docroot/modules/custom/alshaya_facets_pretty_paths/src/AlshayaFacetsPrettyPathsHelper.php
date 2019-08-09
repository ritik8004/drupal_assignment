<?php

namespace Drupal\alshaya_facets_pretty_paths;

/**
 * Utilty Class.
 */
class AlshayaFacetsPrettyPathsHelper {

  /**
   * Encode url components according to given rules.
   *
   * @param string $element
   *   Raw element value.
   *
   * @return string
   *   Encoded element.
   */
  public static function encodeFacetUrlComponents($element) {
    // Convert to lowercase.
    $element = strtolower($element);

    // Convert spaces to '_'.
    $element = str_replace(' ', '_', $element);

    // Convert - in the facet value to '__'.
    $element = str_replace('-', '__', $element);

    return $element;

  }

  /**
   * Decode url components according to given rules.
   *
   * @param string $element
   *   Encoded element value.
   *
   * @return string
   *   Raw element.
   */
  public static function decodeFacetUrlComponents($element) {
    // Capitalize first letter.
    $element = ucfirst($element);

    // Convert _ to spaces.
    $element = str_replace('_', ' ', $element);

    // Convert __ in the facet value to '-'.
    $element = str_replace('__', '-', $element);

    return $element;
  }

}
