<?php

/**
 * @file
 * Contains \Drupal\acq_sku\CategoryManagerInterface
 */

namespace Drupal\acq_sku;

/**
 * Provides an interface for commerce category tree to taxonomy
 * synchronization.
 *
 * @ingroup acq_sku
 */
interface CategoryManagerInterface {

  /**
   * synchronizeTree
   *
   * Synchronize a taxonomy vocabulary based on a commerce backend
   * category tree.
   *
   * @param string $vocabulary Vocabulary name for category tree
   * @param int $remoteRoot Remote root category ID (optional)
   *
   * @return array $results
   */
  public function synchronizeTree($vocabulary, $remoteRoot = NULL);

  /**
   * synchronizeCategory
   *
   * Synchronize a taxonomy vocabulary based on updated commerce
   * backend partial category tree (when a category is moved or
   * updated).
   *
   * @param string $vocabulary Vocabulary name for category tree
   * @param array $categories Conductor category tree data
   *
   * @return array $results
   */
  public function synchronizeCategory($vocabulary, array $categories);
}
