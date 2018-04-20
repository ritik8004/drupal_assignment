<?php

namespace Drupal\alshaya_super_category;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class ProductSuperCategoryTree.
 */
class ProductSuperCategoryTree extends ProductCategoryTree {

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $productCategoryTree;

  /**
   * ProductCategoryTree constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $product_category_tree
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(ProductCategoryTreeInterface $product_category_tree, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, CacheBackendInterface $cache, RouteMatchInterface $route_match, Connection $connection) {
    $this->productCategoryTree = $product_category_tree;
    parent::__construct($entity_type_manager, $language_manager, $cache, $route_match, $connection);
  }

  /**
   * Get top level category items.
   *
   * @param string $langcode
   *   (optional) The language code.
   *
   * @return array
   *   Processed term data.
   */
  public function getCategoryRootTerms($langcode = NULL) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $cid = self::CACHE_ID . '_' . $langcode;

    if ($term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    // Get all child terms for the given parent.
    $term_data = $this->getCategoryTree($langcode, 0, FALSE, FALSE);

    $cache_tags = [
      self::CACHE_TAG,
      self::VOCABULARY_ID,
    ];

    $this->cache->set($cid, $term_data, Cache::PERMANENT, $cache_tags);
    return $term_data;
  }

  /**
   * Get root parent of given term.
   *
   * OR get parent of the term by getting term from current route.
   *
   * @param null|object $term
   *   (optional) The term object or nothing.
   *
   * @return \Drupal\taxonomy\TermInterface|mixed|null
   *   Return the parent term object or NULL.
   */
  public function getCategoryTermRootParent($term = NULL) {
    if (empty($term) || !$term instanceof  TermInterface) {
      $term = $this->getCategoryTermFromRoute();
    }

    if ($term instanceof TermInterface && parent::VOCABULARY_ID == $term->bundle()) {
      $parents = $this->getSuperCategoryMapping();
      // Get the top level parent id if parent exists.
      return isset($parents[$term->id()]) ? $parents[$term->id()] : NULL;
    }

    return NULL;
  }

  /**
   * Cache super category term mapping.
   */
  protected function getSuperCategoryMapping() {
    if ($cache_terms = $this->cache->get('super_category_map')) {
      return $cache_terms->data;
    }

    $terms = $this->getCategoryRootTerms();
    $cache_terms = [];
    $cache_terms += $terms;
    // Loop through each parent to map parent key to child.
    foreach (array_keys($terms) as $tid) {
      $childterms = $this->termStorage->loadTree('acq_product_category', $tid, NULL, TRUE);
      foreach ($childterms as $childterm) {
        $cache_terms[$childterm->id()] = $terms[$tid];
      }
    }

    $this->cache->set('super_category_map', $cache_terms, Cache::PERMANENT, [
      ProductCategoryTree::CACHE_TAG,
      ProductCategoryTree::VOCABULARY_ID,
    ]);
    return $cache_terms;
  }

}
