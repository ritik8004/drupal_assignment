<?php

namespace Drupal\alshaya_product_options;

use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class ProductOptionsHelper.
 *
 * @package Drupal\alshaya_product_options
 */
class ProductOptionsHelper {

  const CID_SIZE_GROUP = 'alshaya_size_group';

  /**
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  protected $skuFieldsManager;

  /**
   * I18nHelper object.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  protected $i18nHelper;

  /**
   * Production Options Manager service object.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  protected $productOptionsManager;

  /**
   * Alshaya API Wrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Swatches Helper service object.
   *
   * @var \Drupal\alshaya_product_options\SwatchesHelper
   */
  protected $swatches;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Cache Backend service for product_options.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  protected $syncedOptions = [];

  /**
   * ProductOptionsHelper constructor.
   *
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\acq_sku\ProductOptionsManager $product_options_manager
   *   Production Options Manager service object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya API Wrapper service object.
   * @param \Drupal\alshaya_product_options\SwatchesHelper $swatches
   *   Swatches Helper service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for product_options.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(SKUFieldsManager $sku_fields_manager,
                              I18nHelper $i18n_helper,
                              ProductOptionsManager $product_options_manager,
                              AlshayaApiWrapper $api_wrapper,
                              SwatchesHelper $swatches,
                              LanguageManagerInterface $language_manager,
                              Connection $connection,
                              CacheBackendInterface $cache,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelInterface $logger) {
    $this->skuFieldsManager = $sku_fields_manager;
    $this->i18nHelper = $i18n_helper;
    $this->productOptionsManager = $product_options_manager;
    $this->apiWrapper = $api_wrapper;
    $this->swatches = $swatches;
    $this->languageManager = $language_manager;
    $this->connection = $connection;
    $this->cache = $cache;
    $this->configFactory = $config_factory;
    $this->logger = $logger;
  }

  /**
   * Synchronize all product options.
   */
  public function synchronizeProductOptions() {
    $this->logger->debug('Sync for all product attribute options started.');
    $fields = $this->skuFieldsManager->getFieldAdditions();

    // We only want to sync attributes.
    $fields = array_filter($fields, function ($field) {
      return ($field['parent'] == 'attributes');
    });

    // For existing live sites we might have source empty.
    array_walk($fields, function (&$field, $field_code) {
      if (empty($field['source'])) {
        $field['source'] = $field_code;
      }
    });

    $sync_options = array_column($fields, 'source');

    foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
      foreach ($sync_options as $attribute_code) {
        $this->syncProductOption($attribute_code, $langcode);
      }
    }

    // We won't do this cleanup for single sync.
    // All code here is only till the time we get it working through ACM.
    // And single option sync `drush sync-option` is only for testing.
    // On prod we do sync of all options only.
    $this->productOptionsManager->deleteUnavailableOptions($this->syncedOptions);
    $this->logger->debug('Sync for all product attribute options finished.');
  }

  /**
   * Sync specific attribute's options for particular language.
   *
   * @param string $attribute_code
   *   Attribute code.
   * @param string $langcode
   *   Language code.
   */
  public function syncProductOption($attribute_code, $langcode) {
    $this->apiWrapper->updateStoreContext($langcode);

    $this->logger->debug('Sync for product attribute options started of attribute @attribute_code in language @langcode.', [
      '@attribute_code' => $attribute_code,
      '@langcode' => $langcode,
    ]);

    try {
      // First get attribute info.
      $attribute = $this->apiWrapper->getProductAttributeWithSwatches($attribute_code);
      $attribute = json_decode($attribute, TRUE);
    }
    catch (\Exception $e) {
      // For now we have many fields in sku_base_fields which are not
      // available in all brands.
      return;
    }

    if (empty($attribute) || empty($attribute['options'])) {
      return;
    }

    $swatches = [];
    foreach ($attribute['swatches'] as $swatch) {
      $swatches[$swatch['option_id']] = $swatch;
    };

    $weight = 0;
    foreach ($attribute['options'] as $option) {
      if (empty($option['value'])) {
        continue;
      }

      $term = $this->productOptionsManager->createProductOption(
        $langcode,
        $option['value'],
        $option['label'],
        $attribute['attribute_id'],
        $attribute['attribute_code'],
        $weight++
      );

      if (empty($term)) {
        continue;
      }

      $this->syncedOptions[$attribute_code][$option['value']] = $option['value'];

      // Check if we have value for swatch and it is changed, we trigger
      // save only if value changed.
      if (isset($swatches[$option['value']])) {
        $this->swatches->updateAttributeOptionSwatch($term, $swatches[$option['value']]);
      }

      // Check if we have value for multi size and it is changed, we trigger
      // save only if value changed.
      if (isset($attribute['extension_attributes']['size_chart'])) {
        $this->updateAttributeOptionSize($term, $attribute['extension_attributes']);
      }
    }

    $this->logger->debug('Sync for product attribute options finished of attribute @attribute_code in language @langcode.', [
      '@attribute_code' => $attribute_code,
      '@langcode' => $langcode,
    ]);

    $this->apiWrapper->resetStoreContext();
  }

  /**
   * Update Term with Attribute option value if changed.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Taxonomy term.
   * @param array $attributes_info
   *   Attributes info array received from API.
   */
  public function updateAttributeOptionSize(TermInterface $term, array $attributes_info) {
    $size_chart = $term->get('field_attribute_size_chart')->getString();
    $size_chart_label = $term->get('field_attribute_size_chart_label')->getString();
    $size_group = $term->get('field_attribute_size_group')->getString();

    // Size chart is like a flag, if set to zero - we ignore group and label.
    if (empty($attributes_info['size_chart'])) {
      $attributes_info['size_chart_label'] = '';
      $attributes_info['size_group'] = '';
    }

    if ($size_chart != $attributes_info['size_chart']
      || $size_chart_label != $attributes_info['size_chart_label']
      || $size_group != $attributes_info['size_group']) {

      $term->get('field_attribute_size_chart')->setValue($attributes_info['size_chart']);
      $term->get('field_attribute_size_chart_label')->setValue($attributes_info['size_chart_label']);
      $term->get('field_attribute_size_group')->setValue($attributes_info['size_group']);
      $term->save();

      // Delete the cache for size groups mapping, we will re-create it when
      // accessed again.
      foreach ($this->languageManager->getLanguages() as $language) {
        $this->cache->delete(self::CID_SIZE_GROUP . ':' . $language->getId());
      }
    }
  }

  /**
   * Get alternative attribute codes.
   *
   * @param string $attribute_code
   *   Attribute code to get alternatives for.
   *
   * @return array
   *   Alternative attributes in particular group for an attribute.
   */
  public function getSizeGroupAlternateAttributes(string $attribute_code) {
    $group = $this->getSizeGroup($attribute_code);

    if ($group) {
      return array_values($group);
    }

    return [];
  }

  /**
   * Get all attributes in particular group.
   *
   * @param string $attribute_code
   *   Attribute code to get group to get all attributes in it.
   *
   * @return array
   *   Attributes for particular group.
   */
  public function getSizeGroup(string $attribute_code) {
    $groups = $this->getSizeGroups();

    foreach ($groups ?? [] as $attributes) {
      if (isset($attributes[$attribute_code])) {
        return $attributes;
      }
    }

    return [];
  }

  /**
   * Wrapper function to get size groups.
   *
   * @return array
   *   Multi-dimensional array with Group names as key and Attribute codes.
   */
  private function getSizeGroups() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $cid = self::CID_SIZE_GROUP . ':' . $langcode;
    $cache = $this->cache->get($cid);
    if (isset($cache->data)) {
      return $cache->data;
    }

    $groups = [];

    $query = $this->connection->select('taxonomy_term__field_sku_attribute_code', 'attribute_code');
    $query->join('taxonomy_term__field_attribute_size_group', 'size_group', 'attribute_code.entity_id = size_group.entity_id');
    $query->join(
      'taxonomy_term__field_attribute_size_chart_label',
      'size_chart_label',
      'attribute_code.entity_id = size_chart_label.entity_id AND size_chart_label.langcode = :langcode',
      [':langcode' => $langcode]
    );
    $query->addField('size_group', 'field_attribute_size_group_value', 'size_group');
    $query->addField('attribute_code', 'field_sku_attribute_code_value', 'attribute_code');
    $query->addField('size_chart_label', 'field_attribute_size_chart_label_value', 'size_chart_label');
    $query->groupBy('size_group');
    $query->groupBy('attribute_code');
    $query->groupBy('size_chart_label');
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($result as $row) {
      $groups[$row['size_group']][$row['attribute_code']] = $row['size_chart_label'];
    }

    // As discussed in ticket, we are hard coding this for now.
    // We can change it to config or ask sequence from Magento later
    // when required.
    $sorts = $this->configFactory
      ->get('alshaya_product_options.settings')
      ->get('group_sequence') ?? [];
    $sorts = array_flip($sorts);

    foreach ($groups as &$attributes) {
      // We get the attribute names like size_shoe_uk, size_show_eu, etc.
      // We expect the code to be two characters and as a suffix in the
      // attribute code. Currently we don't get the order in which we need to
      // display in frontend so we have added a config where we store the
      // sequence. Below we sort the attributes based on the sequence in config.
      uksort($attributes, function ($a, $b) use ($sorts) {
        $a_suffix = substr($a, -2);
        $b_suffix = substr($b, -2);

        // Avoid notices and warnings if we get different attributes.
        if (!isset($sorts[$a_suffix]) || !isset($sorts[$b_suffix])) {
          return 0;
        }

        return $sorts[$a_suffix] > $sorts[$b_suffix] ? 1 : -1;
      });
    }

    $this->cache->set($cid, $groups);

    return $groups;
  }

}
