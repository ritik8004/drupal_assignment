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
    $placeholder_tid = $this->configFactory->get('rcs_placeholders.settings')->get('category.placeholder_tid');

    $super_categories = $this->termStorage->loadTree('rcs_category', 0, 1, TRUE);
    $term_data = [];
    foreach ($super_categories as $category) {
      if ($placeholder_tid === $category->id()) {
        continue;
      }
      $category = $category->getTranslation($langcode);
      $mdc_id = $category->get('field_commerce_id')->getString();
      $term_data[$mdc_id] = [
        'id' => $mdc_id,
        'label' => $category->getName(),
      ];
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
      $url_keys = [$url_key, $url_key . '/'];
      $query = $this->termStorage->getQuery();
      $query->condition('vid', 'rcs_category');
      $query->condition('field_category_slug', $url_keys, 'IN');
      $tids = $query->execute();
      if (!empty($tids)) {
        $term = $this->termStorage->load(current($tids));
        return [
          'id' => $term->get('field_commerce_id')->getString(),
          'label' => $term->getName(),
          'path' => '/' . $term->get('field_category_slug')->getString(),
        ];
      }
    }

    $super_categories = $this->termStorage->loadTree('rcs_category', 0, 1, TRUE);
    $placeholder_tid = $this->configFactory->get('rcs_placeholders.settings')->get('category.placeholder_tid');

    if (!empty($super_categories)) {
      foreach ($super_categories as $key => $categories_terms) {
        if ($categories_terms->id() === $placeholder_tid) {
          unset($super_categories[$key]);
          break;
        }
      }
      $term = current($super_categories);
      return [
        'id' => $term->get('field_commerce_id')->getString(),
        'label' => $term->getName(),
        'path' => '/' . $term->get('field_category_slug')->getString(),
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
    $names = [$name, $name . '/'];
    $query = $this->termStorage->getQuery();
    $query->condition('vid', 'rcs_category');
    $query->condition('field_category_slug', $names, 'IN');
    $query->condition('langcode', $langcode);
    $tids = $query->execute();
    if (!empty($tids)) {
      $term = $this->termStorage->load(current($tids));
      return (!empty($term)) ? $term->id() : NULL;
    }
  }

}
