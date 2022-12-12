<?php

namespace Drupal\alshaya_acm_product_category\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Event that is fired when processing categories data for categories API.
 */
class GetEnrichedCategoryDataEvent extends Event {

  public const EVENT_NAME = 'enriched_category_data';

  /**
   * The current langcode.
   *
   * @var string
   */
  protected $langcode;

  /**
   * The term data.
   *
   * @var array
   */
  protected $data;

  /**
   * Cache metadata.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $cacheData;

  /**
   * Constructs the object.
   *
   * @param string $langcode
   *   Langcode value.
   */
  public function __construct(string $langcode) {
    $this->langcode = $langcode;
  }

  /**
   * Get term data.
   *
   * @return array
   *   The term data.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Set term data.
   *
   * @param array $data
   *   The term data.
   */
  public function setData(array $data) {
    $this->data = $data;
  }

  /**
   * Get currently set langcode.
   *
   * @return string
   *   Langcode value.
   */
  public function getLangcode() {
    return $this->langcode;
  }

  /**
   * Get cacheability metadata.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   Cache metadata.
   */
  public function getCacheabilityMetadata() {
    return $this->cacheData;
  }

  /**
   * Set cacheability metadata.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cache_data
   *   Set cacheability metadata.
   */
  public function setCacheabilityMetadata(CacheableMetadata $cache_data) {
    $this->cacheData = $cache_data;
  }

}
