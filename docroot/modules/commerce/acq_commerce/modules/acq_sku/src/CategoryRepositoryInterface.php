<?php

namespace Drupal\acq_sku;

/**
 * Provides an interface for category tree to taxonomy synchronization.
 *
 * @ingroup acq_sku
 */
interface CategoryRepositoryInterface {

  /**
   * Get Term Id from Commerce Id.
   *
   * @param int $commerce_id
   *   Commerce Backend ID.
   *
   * @return int|null
   *   Return found term id or null if not found.
   *
   * @throws \RuntimeException
   */
  public function getTermIdFromCommerceId($commerce_id);

  /**
   * Get Term Id from Commerce Id.
   *
   * @param array $commerce_ids
   *   Array Commerce Backend ID.
   *
   * @return array
   *   Return found term ids.
   *
   * @throws \RuntimeException
   */
  public function getTermIdsFromCommerceIds(array $commerce_ids);

  /**
   * LoadCategoryTerm.
   *
   * Load a Taxonomy term representing a category by commerce ID.
   *
   * @param int $commerce_id
   *   Commerce Backend ID.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   Return found term or null if not found.
   *
   * @throws \RuntimeException
   */
  public function loadCategoryTerm($commerce_id);

  /**
   * SetVocabulary.
   *
   * Set the vocabulary name of the taxonomy used for category sync.
   *
   * @param string $vocabulary
   *   Taxonomy vocabulary.
   *
   * @return self
   *   Return self.
   */
  public function setVocabulary($vocabulary);

}
