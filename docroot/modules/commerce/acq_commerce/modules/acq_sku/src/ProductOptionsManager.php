<?php

namespace Drupal\acq_sku;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\acq_commerce\Conductor\ClientFactory;

/**
 * Provides a service for product options data to taxonomy synchronization.
 *
 * @ingroup acq_sku
 */
class ProductOptionsManager {

  /**
   * Conductor Agent Category Data API Endpoint.
   *
   * @const CONDUCTOR_API_CATEGORY
   */
  const PRODUCT_OPTIONS_VOCABULARY = 'sku_product_option';

  /**
   * Taxonomy Term Entity Storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  private $termStorage;

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  private $apiWrapper;

  /**
   * Result (create / update / failed) counts.
   *
   * @var array
   */
  private $results;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, APIWrapper $api_wrapper, LoggerChannelFactoryInterface $logger_factory) {

    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('acq_sku');
  }

  /**
   * Load existing term (if available).
   *
   * @param string $attribute_code
   *   Attribute code - Magento value.
   * @param int $option_id
   *   Option id - Magento value.
   * @param bool $log_error
   *   Flag to stop logging term not found errors during sync.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Loaded taxonomy term object if found.
   */
  public function loadProductOptionByOptionId($attribute_code, $option_id, $log_error = TRUE) {
    $query = $this->termStorage->getQuery();
    $query->condition('field_sku_option_id', $option_id);
    $query->condition('field_sku_attribute_code', $attribute_code);
    $query->condition('vid', self::PRODUCT_OPTIONS_VOCABULARY);
    $tids = $query->execute();

    // We won't log no term found error during sync.
    if (count($tids) === 0) {
      if ($log_error) {
        $this->logger->error('No term found for option_id: @option_id having attribute_code @attribute_code.', [
          '@option_id' => $option_id,
          '@attribute_code' => $attribute_code
        ]);
      }
      return NULL;
    }
    elseif (count($tids) > 1) {
      $this->logger->critical('Multiple terms found for option_id: @option_id having attribute_code @attribute_code.', [
        '@option_id' => $option_id,
        '@attribute_code' => $attribute_code
      ]);
    }

    // We use the first term and continue even if we have multiple terms.
    $tid = array_shift($tids);
    return $this->termStorage->load($tid);
  }

  protected function createProductOption($option_id, $option_value, $attribute_id, $attribute_code) {
    // Update the term if already available.
    if ($term = $this->loadProductOptionByOptionId($attribute_code, $option_id, FALSE)) {
      if ($term->getName() != $option_value) {
        $term->setName($option_value);
        $term->save();
      }
    }
    else {
      $term = $this->termStorage->create([
        'vid' => self::PRODUCT_OPTIONS_VOCABULARY,
        'name' => $option_value,
        'field_sku_option_id' => $option_id,
        'field_sku_attribute_id' => $attribute_id,
        'field_sku_attribute_code' => $attribute_code,
      ]);

      $term->save();
    }
  }

  /**
   * Synchronize all product options.
   */
  public function synchronizeProductOptions() {
    $option_sets = $this->apiWrapper->getProductOptions();

    foreach ($option_sets as $options) {
      foreach ($options['options'] as $key => $value) {
        $this->createProductOption($key, $value, $options['attribute_id'], $options['attribute_code']);
      }
    }
  }

}
