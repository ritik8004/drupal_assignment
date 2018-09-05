<?php

namespace Drupal\acq_sku;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

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
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, APIWrapper $api_wrapper, LoggerChannelFactoryInterface $logger_factory, I18nHelper $i18n_helper) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('acq_sku');
    $this->i18nHelper = $i18n_helper;
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
  public function loadProductOptionByOptionId($attribute_code, $option_id, $langcode, $log_error = TRUE) {
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
          '@attribute_code' => $attribute_code,
        ]);
      }
      return NULL;
    }
    elseif (count($tids) > 1) {
      $this->logger->critical('Multiple terms found for option_id: @option_id having attribute_code @attribute_code.', [
        '@option_id' => $option_id,
        '@attribute_code' => $attribute_code,
      ]);
    }

    // We use the first term and continue even if we have multiple terms.
    $tid = array_shift($tids);

    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = $this->termStorage->load($tid);

    if ($langcode && $term->hasTranslation($langcode)) {
      $term = $term->getTranslation($langcode);
    }

    return $term;
  }

  /**
   * Create product option if not available or update the name.
   *
   * @param string $langcode
   *   Lang code.
   * @param int $option_id
   *   Option id.
   * @param string $option_value
   *   Value (term name).
   * @param int $attribute_id
   *   Attribute id.
   * @param string $attribute_code
   *   Attribute code.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Term object or null.
   */
  public function createProductOption($langcode, $option_id, $option_value, $attribute_id, $attribute_code, $weight) {
    if (empty($option_value)) {
      $this->logger->warning('Got empty value while syncing production options: @data', [
        '@data' => json_encode([
          'langcode' => $langcode,
          'option_id' => $option_id,
          'attribute_id' => $attribute_id,
          'attribute_code' => $attribute_code,
        ]),
      ]);

      return NULL;
    }

    // Update the term if already available.
    if ($term = $this->loadProductOptionByOptionId($attribute_code, $option_id, NULL, FALSE)) {
      $save_term = FALSE;

      // Save term even if weight changes.
      if ($term->getWeight() != $weight) {
        $save_term = TRUE;
      }

      if ($term->hasTranslation($langcode)) {
        $term = $term->getTranslation($langcode);

        // We won't allow editing name here, if required it must be done from
        // Magento.
        if ($term->getName() != $option_value) {
          $term->setName($option_value);
          $save_term = TRUE;
        }
      }
      else {
        $term = $term->addTranslation($langcode, []);
        $term->setName($option_value);
        $save_term = TRUE;
      }

      if ($save_term) {
        $term->setWeight($weight);
        $term->save();
      }
    }
    else {
      $term = $this->termStorage->create([
        'vid' => self::PRODUCT_OPTIONS_VOCABULARY,
        'langcode' => $langcode,
        'name' => $option_value,
        'weight' => $weight,
        'field_sku_option_id' => $option_id,
        'field_sku_attribute_id' => $attribute_id,
        'field_sku_attribute_code' => $attribute_code,
      ]);

      $term->save();
    }

    return $term;
  }

  /**
   * Synchronize all product options.
   */
  public function synchronizeProductOptions() {
    $options_available = [];

    foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
      $this->apiWrapper->updateStoreContext($store_id);
      $option_sets = $this->apiWrapper->getProductOptions();

      $weight = 0;
      foreach ($option_sets as $options) {
        foreach ($options['options'] as $key => $value) {
          $this->createProductOption($langcode, $key, $value, $options['attribute_id'], $options['attribute_code'], $weight++);
          $options_available[$options['attribute_code']][$options['attribute_id']] = $options['attribute_id'];
        }
      }
    }

    try {
      $this->deleteUnavailableOptions($options_available);
    }
    catch (\Exception $e) {
      $this->logger->error(t('Error occurred while deleting options not available in MDC. Error: @message', [
        '@message' => $e->getMessage(),
      ]));
    }
  }

  /**
   * Delete all the options that are no longer available in MDC.
   *
   * @param array $options_available
   *   Multi-dimensional array containing attribute codes as key and option ids
   *   currently available in values.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteUnavailableOptions(array $options_available) {
    foreach ($options_available as $attribute_code => $ids) {
      $query = $this->termStorage->getQuery();
      $query->condition('field_sku_option_id', $ids, 'NOT IN');
      $query->condition('field_sku_attribute_code', $attribute_code);
      $query->condition('vid', self::PRODUCT_OPTIONS_VOCABULARY);
      $tids = $query->execute();

      if ($tids) {
        $this->termStorage->resetCache();
        $entities = $this->termStorage->loadMultiple($tids);
        $this->termStorage->delete($entities);
      }
    }
  }

}
