<?php

namespace Drupal\acq_sku;

/**
 * Provides an interface for category tree to taxonomy synchronization.
 *
 * @ingroup acq_sku
 */
interface CategoryManagerInterface {

  /**
   * SynchronizeTree.
   *
   * Synchronize a taxonomy vocabulary based on a commerce backend
   * category tree.
   *
   * @param string $vocabulary
   *   Vocabulary name for category tree.
   * @param int $remoteRoot
   *   Remote root category ID (optional)
   *
   * @return array
   *   Array of results.
   */
  public function synchronizeTree($vocabulary, $remoteRoot = NULL);

  /**
   * SynchronizeCategory.
   *
   * Synchronize a taxonomy vocabulary based on updated commerce
   * backend partial category tree (when a category is moved or
   * updated).
   *
   * @param string $vocabulary
   *   Vocabulary name for category tree.
   * @param array $categories
   *   Conductor category tree data.
   *
   * @return array
   *   Array of results.
   */
  public function synchronizeCategory($vocabulary, array $categories);

  /**
   * Loads the category data from Mangento.
   *
   * @param string $langcode
   *   The langcode for which we should get categories.
   *
   * @return array
   *   Array of categories.
   */
  public function loadCategoryData(string $langcode);

}
