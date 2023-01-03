<?php

namespace Drupal\alshaya_rcs_super_category\Service;

use Drupal\alshaya_super_category\ProductSuperCategoryTree;
use Drupal\Core\Cache\Cache;
use Drupal\taxonomy\TermInterface;

/**
 * Overidden super category tree service.
 */
class RcsProductCategoryTree extends ProductSuperCategoryTree {

  public const VOCABULARY_ID = 'rcs_category';

  public const CACHE_TAG = 'taxonomy_term:rcs_category';

  /**
   * {@inheritdoc}
   */
  public function getCategoryRootTerms($langcode = NULL) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $cid = static::CACHE_ID . '_' . $langcode;

    if ($term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    $placeholder_tid = $this->configFactory->get('rcs_placeholders.settings')->get('category.placeholder_tid');

    $super_categories = $this->termStorage->loadTree('rcs_category', 0, 1, TRUE);
    $term_data = [];
    foreach ($super_categories as $category) {
      $mdc_id = $category->get('field_commerce_id')->getString();

      if ($placeholder_tid === $category->id() || empty($mdc_id)) {
        continue;
      }

      $term_data[$category->id()] = [
        'id' => $category->id(),
        'commerce_id' => $mdc_id,
        'label' => $category->getName(),
        'position' => (int) $category->get('weight')->getString(),
      ];
    }

    uasort($term_data, fn($item1, $item2) => $item1['position'] <=> $item2['position']);

    $this->cache->set($cid, $term_data, Cache::PERMANENT, [static::CACHE_TAG]);
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
      if ($term instanceof TermInterface) {
        return [
          'id' => $term->get('field_commerce_id')->getString(),
          'label' => $term->getName(),
          'path' => '/' . $term->get('field_category_slug')->getString(),
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCategoryTermFromRoute(bool $check_acq_terms = TRUE) {
    $term = &drupal_static('super_category_term');

    if (isset($term)) {
      return $term;
    }

    $term_from_route = parent::getCategoryTermFromRoute(FALSE);
    if ($term_from_route instanceof TermInterface) {
      $term = $term_from_route;
      return $term;
    }

    return NULL;
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
    $query->condition('vid', static::VOCABULARY_ID);
    $query->condition('field_category_slug', $names, 'IN');
    $query->condition('langcode', $langcode);
    $tids = $query->execute();
    return (!empty($tids)) ? current($tids) : NULL;
  }

  /**
   * Get rcs category term by path.
   *
   * @param string $path
   *   Rcs category slug.
   */
  public function getTermByPath($path) {
    $term = NULL;
    if ($tid = $this->getTermByName($path)) {
      $term = $this->termStorage->load($tid);
    }
    return ($term instanceof TermInterface) ? $term : NULL;
  }

}
