<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class Product Category Tree.
 */
class AlshayaRcsMenuTree implements AlshayaRcsMenuTreeInterface {

  const CACHE_BIN = 'alshaya_rcs';

  const CACHE_ID_PH = 'category_ph_tree';

  const VOCABULARY_ID = 'rcs_category';

  const CACHE_TAG = 'taxonomy_term:rcs_category';

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Cache Backend service for alshaya.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * ProductCategoryTree constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $configFactory
   *   Config Factory.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager, CacheBackendInterface $cache, ConfigFactoryInterface $configFactory) {
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->languageManager = $languageManager;
    $this->cache = $cache;
    $this->configFactory = $configFactory;
  }

  /**
   * Get the term tree for 'product_category' vocabulary from cache or fresh.
   *
   * @return array
   *   Processed term data from cache if available or fresh.
   */
  public function getRcsCategoryPlaceholderTerm() {
    $cid = self::CACHE_ID_PH;

    $termData = $this->cache->get($cid);
    if ($termData) {
      return $termData->data;
    }

    $termData = $this->getPlaceholderTerm();

    // @todo Check for cache invalidation.
    $this->cache->set($cid, $termData);

    return $termData;
  }

  /**
   * Get the term tree for 'product_category' vocabulary.
   *
   * @return array
   *   Processed term data.
   */
  protected function getPlaceholderTerm() {
    $config = $this->configFactory->get('rcs_placeholders.settings');
    $placeholderTermId = $config->get('category.placeholder_tid');

    if (!$placeholderTermId) {
      return [];
    }

    return $this->termStorage->load($placeholderTermId);
  }

}
