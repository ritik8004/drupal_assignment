<?php

namespace Drupal\alshaya_rcs_super_category\Service;

use Drupal\alshaya_super_category\ProductSuperCategoryTree;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Database\Connection;

/**
 * Overidden super category tree service.
 */
class RcsProductCategoryTree extends ProductSuperCategoryTree {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Construct RcsProductCategoryTree.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request Stack service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database service.
   */
  public function __construct(
    RequestStack $request_stack,
    LanguageManagerInterface $language_manager,
    EntityTypeManagerInterface $entity_type_manager,
    CacheBackendInterface $cache,
    Connection $connection
  ) {
    $this->requestStack = $request_stack;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->cache = $cache;
    $this->connection = $connection;
  }

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

    $super_categories = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('rcs_category', 0, 1, TRUE);
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

    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    if (!empty($url_key)) {
      $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
      $query->condition('vid', 'rcs_category');
      $query->condition('field_category_slug', $url_key);
      $tids = $query->execute();
      if (!empty($tids)) {
        $term = $term_storage->load(current($tids));
        return [
          'id' => $term->id(),
          'label' => $term->getName(),
          'path' => $term->get('path')->getString(),
        ];
      }
    }

    $super_categories = $term_storage->loadTree('rcs_category', 0, 1, TRUE);

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

}
