<?php

namespace Drupal\alshaya_stores_finder;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;

/**
 * Class StoresFinderUtility.
 */
class StoresFinderUtility {

  /**
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $nodeStorage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new StoresFinderUtility object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, LoggerChannelFactoryInterface $logger_factory) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->languageManager = $language_manager;
    $this->logger = $logger_factory->get('alshaya_stores_finder');
  }

  /**
   * Utility function to get store node from store code.
   *
   * @param string $store_code
   *   Store code.
   * @param bool $log_not_found
   *   Log errors when store not found. Can be false during sync.
   *
   * @return \Drupal\node\Entity\Node
   *   Store node.
   */
  public function getStoreFromCode($store_code, $log_not_found = TRUE) {
    $query = $this->nodeStorage->getQuery();
    $query->condition('field_store_locator_id', $store_code);
    $ids = $query->execute();

    // No stores found.
    if (count($ids) === 0) {
      if ($log_not_found) {
        $this->logger->error('No store node found for store code: @store_code.', ['@store_code' => $store_code]);
      }
      return NULL;
    }
    // Some issue in DATA.
    elseif (count($ids) > 1) {
      $this->logger->error('Multiple store nodes found for store code: @store_code.', ['@store_code' => $store_code]);
    }

    // Get the first id.
    $nid = array_shift($ids);

    return $this->nodeStorage->load($nid);
  }

  /**
   * Utility function to get translated store node from store code.
   *
   * @param string $store_code
   *   Store code.
   *
   * @return \Drupal\node\Entity\Node
   *   Store node.
   */
  public function getTranslatedStoreFromCode($store_code) {
    if ($store_node = $this->getStoreFromCode($store_code)) {

      $langcode = $this->languageManager->getCurrentLanguage()->getId();

      if ($store_node->hasTranslation($langcode)) {
        $store_node = $store_node->getTranslation($langcode);
      }

      return $store_node;
    }

    return NULL;
  }

  /**
   * Get extra data for the given store from node.
   *
   * @param array $store_data
   *   The store data.
   * @param object|null $store_node
   *   The store node object if available.
   *
   * @return array
   *   Return the store array with additional data from store node.
   */
  public function getStoreExtraData(array $store_data, $store_node = NULL) {
    if (!empty($store_data) && empty($store_node)) {
      $store_node = $this->getTranslatedStoreFromCode($store_data['code']);
    }

    $store = [];
    if ($store_node) {
      $store['name'] = $store_node->label();
      $store['code'] = $store_node->get('field_store_locator_id')->getString();
      $store['address'] = $store_node->get('field_store_address')->getString();
      $store['phone_number'] = $store_node->get('field_store_phone')->getString();
      $store['open_hours'] = $store_node->get('field_store_open_hours')->getValue();
      $store['delivery_time'] = $store_node->get('field_store_sts_label')->getString();
      $store['nid'] = $store_node->id();
      $store['view_on_map_link'] = Url::fromRoute('alshaya_click_collect.cc_store_map_view', ['node' => $store_node->id()])->toString();

      if ($lat_lng = $store_node->get('field_latitude_longitude')->getValue()) {
        $store['lat'] = $lat_lng[0]['lat'];
        $store['lng'] = $lat_lng[0]['lng'];
      }
    }
    return $store;
  }

  /**
   * Function to create/update single store.
   *
   * @param array $store
   *   Store array.
   * @param string $langcode
   *   Language code.
   */
  public function updateStore(array $store, $langcode) {
    if ($node = $this->getStoreFromCode($store['store_code'], FALSE)) {
      if ($node->hasTranslation($langcode)) {
        $node = $node->getTranslation($langcode);
        $this->logger->info('Updating store @store_code and @langcode', ['@store_code' => $store['store_code'], '@langcode' => $langcode]);
      }
      else {
        $node = $node->addTranslation($langcode);
        $this->logger->info('Adding @langcode translation for store @store_code', ['@store_code' => $store['store_code'], '@langcode' => $langcode]);
      }
    }
    else {
      $node = $this->nodeStorage->create([
        'type' => 'store',
      ]);

      $node->get('langcode')->setValue($langcode);

      $node->get('field_store_locator_id')->setValue($store['store_code']);

      $this->logger->info('Creating store @store_code in @langcode', ['@store_code' => $store['store_code'], '@langcode' => $langcode]);
    }

    if (!empty($store['store_name'])) {
      $node->get('title')->setValue($store['store_name']);
    }
    else {
      $node->get('title')->setValue($store['store_code']);
    }

    $node->get('field_latitude_longitude')->setValue(['lat' => $store['latitude'], 'lng' => $store['longitude']]);

    $node->get('field_store_phone')->setValue($store['store_phone']);

    if (isset($store['address'])) {
      $node->get('field_store_address')->setValue($store['address']);
    }
    else {
      $node->get('field_store_address')->setValue('');
    }

    $node->get('field_store_area')->setValue($store['area']);

    if (isset($store['sts_delivery_time_label'])) {
      $node->get('field_store_sts_label')->setValue($store['sts_delivery_time_label']);
    }
    else {
      $node->get('field_store_sts_label')->setValue('');
    }

    $open_hours = [];

    foreach ($store['store_hours'] as $store_hour) {
      $open_hours[] = [
        'key' => $store_hour['day'],
        'value' => $store_hour['hours'],
      ];
    }

    $node->get('field_store_open_hours')->setValue($open_hours);

    // Set the status.
    $node->setPublished((bool) $store['status']);

    $node->save();
  }

}
