<?php

namespace Drupal\alshaya_rcs_product\Services;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\acq_sku\ProductOptionsManager;

/**
 * Contains helper methods to fetch product options.
 */
class AlshayaRcsProductAttributesHelper {

  /**
   * RCS Product Helper.
   *
   * @var \Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper
   */
  protected $rcsProductHelper;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs RCS Product Attribute Helper service.
   *
   * @param \Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper $rcs_product_helper
   *   RCS Product Helper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(
    AlshayaRcsProductHelper $rcs_product_helper,
    LoggerChannelFactoryInterface $logger_factory,
    LanguageManagerInterface $language_manager,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->rcsProductHelper = $rcs_product_helper;
    $this->logger = $logger_factory->get('alshaya_rcs_product');
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Returns  product attributes options.
   */
  public function getProductAttributesOptions() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Fetch product options.
    $product_attributes = $this->rcsProductHelper->getProductOptionsQueryVariables();
    $product_attributes = array_column($product_attributes, 'attribute_code');
    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('field_sku_attribute_code', $product_attributes, 'IN');
    $query->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY);
    $query->condition('langcode', $langcode);
    $tids = $query->execute();

    if (empty($tids)) {
      return [];
    }

    // Populate product options array.
    $items = [];

    $product_option_entities = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($tids);
    foreach ($product_option_entities as $product_option) {
      /** @var \Drupal\taxonomy\TermInterface $product_option */
      $product_option = ($product_option->language()->getId() === $langcode)
        ? $product_option
        : $product_option->getTranslation($langcode);

      $product_option_en = $product_option->getTranslation('en');

      $attribute_code = $product_option->get('field_sku_attribute_code')->getString();

      $items[$attribute_code][] = [
        'attribute_code' => $attribute_code,
        'value' => $product_option->get('field_sku_option_id')->getString(),
        'label' => $product_option->label(),
        'gtm_label' => $product_option_en->label(),
        'weight' => intval($product_option->getWeight()),
      ];
    }

    // Sort all the attributes.
    foreach ($items as &$attribute_options) {
      usort($attribute_options, fn($option1, $option2) => $option1['weight'] <=> $option2['weight']);
    }

    return $items;
  }

}
