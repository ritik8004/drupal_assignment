<?php

namespace Drupal\alshaya_rcs_super_category\Service;

use Drupal\alshaya_super_category\ProductSuperCategoryTree;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Cache\Cache;

/**
 * Overidden super category tree service.
 */
class RcsProductCategoryTree extends ProductSuperCategoryTree {

  /**
   * {@inheritdoc}
   */
  public function getCategoryRootTerms($langcode = NULL) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $cid = ProductCategoryTree::CACHE_ID . '_' . $langcode;

    if ($term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    $super_categories = $this->termStorage->loadTree('rcs_category', 0, 1, TRUE);
    $term_data = [];
    foreach ($super_categories as $categories) {
      $term_data[] = $categories->getTranslation($langcode);
    }

    $this->cache->set($cid, $term_data, Cache::PERMANENT, [ProductCategoryTree::CACHE_TAG]);
    return $term_data;
  }

  /**
   * {@inheritdoc}
   */
  public function getCategoryTermRequired($term = NULL, $langcode = NULL) {
    $path = $this->requestStack->getCurrentRequest()->getPathInfo();
    $path_arr = explode('/', $path);
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Get first url key.
    if ($path_arr[1] === $langcode) {
      $url_key = $path_arr[2];
    }
    else {
      $url_key = $path_arr[1];
    }

    if (!empty($url_key)) {
      $query = $this->termStorage->getQuery();
      $query->condition('vid', 'rcs_category');
      $query->condition('field_category_slug', $url_key);
      $tids = $query->execute();
      if (!empty($tids)) {
        $term = $this->termStorage->load(current($tids));
        return [
          'id' => $term->id(),
          'label' => $term->getName(),
          'path' => $term->get('path')->getString(),
        ];
      }
    }

    $super_categories = $this->termStorage->loadTree('rcs_category', 0, 1, TRUE);

    if (!empty($super_categories)) {
      $term = current($super_categories);
      return [
        'id' => $term->id(),
        'label' => $term->getName(),
        'path' => $term->get('path')->getString(),
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCategoryTermFromRoute(bool $check_acq_terms = TRUE) {
    return parent::getCategoryTermFromRoute(FALSE);
  }

  /**
   * {@inheritdoc}
   */
  protected function getTermByName($name, $langcode = NULL) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }
    $query = $this->termStorage->getQuery();
    $query->condition('vid', 'rcs_category');
    $query->condition('field_category_slug', $name);
    $query->condition('langcode', $langcode);
    $tids = $query->execute();
    if (!empty($tids)) {
      $term = $this->termStorage->load(current($tids));
      return (!empty($term)) ? $term->id() : NULL;
    }
  }

}
