<?php

namespace Drupal\alshaya_acm_product;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class ProductCategoryHelper.
 */
class ProductCategoryHelper {

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * ProductCategoryHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              Connection $connection,
                              LanguageManagerInterface $language_manager) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->connection = $connection;
    $this->languageManager = $language_manager;
  }

  /**
   * Get only enabled terms.
   *
   * @param array $terms
   *   Terms array.
   *
   * @return array
   *   Filtered terms.
   */
  public function filterEnabled(array $terms = []) {
    // Remove disabled terms.
    foreach ($terms as $index => $row) {

      if (empty($row['target_id'])) {
        // If term not found, we unset it.
        unset($terms[$index]);
        continue;
      }

      $term = $this->termStorage->load($row['target_id']);

      if ($term instanceof TermInterface && $term->get('field_commerce_status')->getString()) {
        continue;
      }

      // If term not enabled, we unset it.
      unset($terms[$index]);
    }

    return array_values($terms);
  }

  /**
   * Get most inner term for the first group.
   *
   * @param array $terms
   *   Terms array.
   *
   * @return int|null
   *   Term id.
   */
  public function termTreeGroup(array $terms = []) {
    if (!empty($terms)) {
      $terms = $this->filterEnabled($terms);

      if (empty($terms)) {
        return NULL;
      }

      $root_group = $this->getRootGroup($terms[0]['target_id']);
      $root_group_terms = [];
      foreach ($terms as $term) {
        $root = $this->getRootGroup($term['target_id']);
        if ($root == $root_group) {
          $root_group_terms[] = $term['target_id'];
        }
      }

      return $this->getInnerDepthTerm($root_group_terms);
    }

    return NULL;
  }

  /**
   * Get the root level parent tid of a given term.
   *
   * @param int $tid
   *   Term id.
   *
   * @return int
   *   Root parent term id.
   */
  protected function getRootGroup($tid) {
    $static = &drupal_static('alshaya_pdp_breadcrumb_builder_get_root_group', []);

    if (isset($static[$tid])) {
      return $static[$tid];
    }

    // Recursive call to get parent root parent tid.
    while ($tid > 0) {
      $query = $this->connection->select('taxonomy_term__parent', 'tth');
      $query->fields('tth', ['parent_target_id']);
      $query->condition('tth.entity_id', $tid);
      $parent = $query->execute()->fetchField();
      if ($parent == 0) {
        $static[$tid] = $tid;
        return $tid;
      }

      $tid = $parent;
    }
  }

  /**
   * Get the most inner term term based on the depth.
   *
   * @param array $terms
   *   Array of term ids.
   *
   * @return int
   *   The term id.
   */
  protected function getInnerDepthTerm(array $terms = []) {
    if (empty($terms)) {
      return NULL;
    }

    $static = &drupal_static('alshaya_pdp_breadcrumb_builder_get_root_group', []);
    $term_ids = implode(',', $terms);
    if (isset($static[$term_ids])) {
      return $static[$term_ids];
    }

    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    $depths = $this->connection->select('taxonomy_term_field_data', 'ttfd')
      ->fields('ttfd', ['tid', 'depth_level'])
      ->condition('ttfd.tid', $terms, 'IN')
      ->condition('ttfd.langcode', $current_langcode)
      ->execute()->fetchAllKeyed();

    // Flip key/value.
    $terms = array_flip($terms);
    // Merge two array (overriding depth value).
    $depths = array_replace($terms, $depths);
    // Get all max values and get first one.
    $max_depth = array_keys($depths, max($depths));
    $most_inner_tid = $max_depth[0];

    $static[$term_ids] = $most_inner_tid;

    return $most_inner_tid;
  }

  /**
   * Get the term list to be shown in breadcrumb.
   *
   * @param array $terms
   *   Terms array.
   *
   * @return array
   *   Breadcrumb term list.
   */
  public function getBreadcrumbTermList(array $terms = []) {
    if (!empty($terms)) {
      $inner_term = $this->termTreeGroup($terms);
      if ($inner_term) {
        return $this->termStorage->loadAllParents($inner_term);
      }
    }

    return [];
  }

}
