<?php

namespace Drupal\alshaya_super_category\Cache\Context;

use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;

/**
 * Defines the service for "per super category" caching.
 *
 * Cache context ID: 'super_category'.
 */
class SuperCategoryCacheContext implements CacheContextInterface {

  /**
   * Super Category Manager service.
   *
   * @var \Drupal\alshaya_super_category\AlshayaSuperCategoryManager
   */
  protected $superCategoryManager;

  /**
   * Constructor for SuperCategoryCacheContext.
   *
   * @param \Drupal\alshaya_super_category\AlshayaSuperCategoryManager $super_category_manager
   *   Super Category Manager service.
   */
  public function __construct(AlshayaSuperCategoryManager $super_category_manager) {
    $this->superCategoryManager = $super_category_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Super Category');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $term = $this->superCategoryManager->getCategoryTermFromRoute();
    return $term ? 'super_category:' . $term->id() : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
