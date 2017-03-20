<?php

/**
 * @file
 * Contains \Drupal\acq_sku\CategoryRepositoryInterface
 */

namespace Drupal\acq_sku;

/**
 * Provides an interface for commerce category tree to taxonomy
 * synchronization.
 *
 * @ingroup acq_sku
 */
interface CategoryRepositoryInterface {

  /**
   * loadCategoryTerm
   *
   * Load a Taxonomy term representing a category by commerce ID.
   *
   * @param int $commerce_id Commerce Backend ID
   *
   * @return TermInterface|null $category
   * @throws \RuntimeException
   */
  public function loadCategoryTerm($commerce_id);

  /**
   * setVocabulary
   *
   * Set the vocabulary name of the taxonomy used for category sync.
   *
   * @param string $vocabulary Taxonomy vocabulary
   *
   * @return self
   */
  public function setVocabulary($vocabulary);
}
