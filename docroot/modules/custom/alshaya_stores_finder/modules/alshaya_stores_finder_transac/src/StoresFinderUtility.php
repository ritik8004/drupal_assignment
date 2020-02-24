<?php

namespace Drupal\alshaya_stores_finder_transac;

use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\alshaya_addressbook\AlshayaAddressBookManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class StoresFinderUtility.
 */
class StoresFinderUtility {

  use StringTranslationTrait;

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
   * The database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Address Book Manager service object.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  protected $addressBookManager;

  /**
   * Logger service object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Cache backend for cache.data.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity Repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a new StoresFinderUtility object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address Book Manager service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend for cache.data.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity Repository service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaAddressBookManager $address_book_manager,
    LanguageManagerInterface $language_manager,
    Connection $database,
    LoggerChannelFactoryInterface $logger_factory,
    ModuleHandlerInterface $module_handler,
    CacheBackendInterface $cache,
    ConfigFactoryInterface $config_factory,
    EntityRepositoryInterface $entityRepository
  ) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->addressBookManager = $address_book_manager;
    $this->languageManager = $language_manager;
    $this->database = $database;
    $this->logger = $logger_factory->get('alshaya_stores_finder');
    $this->moduleHandler = $module_handler;
    $this->cache = $cache;
    $this->configFactory = $config_factory;
    $this->entityRepository = $entityRepository;
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
      $store['address'] = $this->getStoreAddress($store_node);
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
   * Get store nodes.
   *
   * @param array $store_codes
   *   The array of store codes.
   * @param string $langcode
   *   (Optional) The language code.
   *
   * @return array
   *   Return array of stores.
   */
  public function getStoreNodes(array $store_codes, $langcode = NULL) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    }
    // Get the nids for given store code with custom query.
    $query = $this->database->select('node_field_data', 'n');
    $query->addField('n', 'nid');
    $query->addField('ns', 'field_store_locator_id_value');
    $query->innerJoin('node__field_store_locator_id', 'ns', 'n.nid = ns.entity_id and n.langcode = ns.langcode');
    $query->condition('ns.field_store_locator_id_value', $store_codes, 'IN');
    $query->condition('n.langcode', $langcode);
    return $query->execute()->fetchAllAssoc('nid', \PDO::FETCH_ASSOC);
  }

  /**
   * Return store extra data info for given store codes.
   *
   * @param array $stores
   *   The array of store from magento api.
   * @param string $langcode
   *   (Optional) The language code.
   *
   * @return array
   *   Return array of stores.
   */
  public function getMultipleStoresExtraData(array $stores, $langcode = NULL) {
    $store_codes = array_keys($stores);
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    }

    $store_nodes = $this->getStoreNodes($store_codes, $langcode);
    // Load multiple nodes all together.
    $nids = array_keys($store_nodes);
    $nodes = $this->nodeStorage->loadMultiple($nids);
    $prepared_stores = [];
    $config = $this->configFactory->get('alshaya_click_collect.settings');
    $address = $this->addressBookManager->getAddressStructureWithEmptyValues();
    // Loop through node and add store address/opening hours/delivery time etc.
    foreach ($nodes as $nid => $node) {
      $node = $this->entityRepository->getTranslationFromContext($node, $langcode);
      $prepared_stores[$nid] = $this->getStoreExtraData($store_codes, $node);
      $store = is_array($stores[$store_nodes[$nid]['field_store_locator_id_value']]) ? $stores[$store_nodes[$nid]['field_store_locator_id_value']] : [];
      $store['rnc_available'] = (int) $store['rnc_available'];
      $store['sts_available'] = (int) $store['sts_available'];
      $store['formatted_distance'] = $this->t('@distance miles', [
        '@distance' => number_format((float) $store['distance'], 2, '.', ''),
      ]);
      if (!empty($store['rnc_available'])) {
        $store['delivery_time'] = $config->get('click_collect_rnc');
      }

      $store['cart_address'] = $address;
      // V1 - we update only area in address.
      $store['cart_address']['extension']['address_area_segment'] = $node->get('field_store_area')->getString();

      // V2 - copy address from Store.
      if ($this->addressBookManager->getDmVersion() == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
        $store_address = $node->get('field_address')->getValue();

        if ($store_address) {
          $store['cart_address'] = $this->addressBookManager->getMagentoAddressFromAddressArray(reset($store_address));
        }
      }

      $prepared_stores[$nid] += $store;
      // Unset the store for which we found the node, so that we can log the
      // store codes for which nodes are missing.
      unset($stores[$store_nodes[$nid]['field_store_locator_id_value']]);
    }

    // Log into Drupal for admins to check missing nodes for the store codes.
    if (!empty($stores)) {
      $this->logger->warning('Received a store in Cart Stores API response which is not yet available in Drupal. Store code: %store_code', [
        '%store_code' => implode(',', array_keys($stores)),
      ]);
    }

    return $prepared_stores;
  }

  /**
   * Function to create/update single store.
   *
   * @param array $store
   *   Store array.
   * @param string $langcode
   *   Language code.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function updateStore(array $store, $langcode) {
    static $user;

    if (empty($user)) {
      $user = user_load_by_name(Settings::get('alshaya_acm_user_username'));
    }

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
    $node->get('field_store_email')->setValue($store['store_email']);

    // Always set the textarea to empty value first.
    $node->get('field_store_address')->setValue('');
    $node->get('field_store_area')->setValue('');

    $open_hours = [];

    if ($this->addressBookManager->getDmVersion() == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      if (isset($store['address']) && !empty($store['address'])) {
        $address = $this->addressBookManager->getAddressArrayFromRawMagentoAddress($store['address']);

        // @TODO: Check if this can be removed from magento.
        unset($address['family_name']);
        unset($address['given_name']);

        $node->get('field_address')->setValue($address);
      }

      foreach ($store['store_hours'] as $store_hour) {
        $open_hours[] = [
          'key' => $store_hour['label'],
          'value' => $store_hour['value'],
        ];
      }
    }
    elseif (isset($store['address'])) {
      $node->get('field_store_address')->setValue($store['address']);
      $node->get('field_store_area')->setValue($store['area']);

      foreach ($store['store_hours'] as $store_hour) {
        $open_hours[] = [
          'key' => $store_hour['day'],
          'value' => $store_hour['hours'],
        ];
      }
    }

    $node->get('field_store_open_hours')->setValue($open_hours);

    if (isset($store['sts_delivery_time_label'])) {
      $node->get('field_store_sts_label')->setValue($store['sts_delivery_time_label']);
    }
    else {
      $node->get('field_store_sts_label')->setValue('');
    }

    $this->moduleHandler->alter('alshaya_stores_finder_store_update', $node, $store);

    // Set the status.
    $node->setPublished((bool) $store['status']);

    // Set node owner to acm user.
    $node->setOwner($user);

    $node->save();
  }

  /**
   * Delete stores with given node ids.
   *
   * @param array $nids
   *   Array of $nids of store bundle.
   */
  public function deleteStores(array $nids = []) {
    // If nothing, no need to process.
    if (empty($nids)) {
      return;
    }

    $nodes = $this->nodeStorage->loadMultiple($nids);
    if (!empty($nodes)) {
      /* @var \Drupal\node\Entity\Node $node */
      foreach ($nodes as $node) {
        try {
          // Delete the node.
          $node->delete();
        }
        catch (\Exception $e) {
          // If something goes wrong.
          $this->logger->error('Unable to delete the @bundle node with id @nid', ['@bundle' => $node->bundle(), '@nid' => $node->id()]);
        }
      }
    }
  }

  /**
   * Get orphan store nodes from store locator ids.
   *
   * @param array $store_locator_ids
   *   Store locator ids.
   *
   * @return array
   *   Orphan store node ids.
   */
  public function getOrphanStores(array $store_locator_ids = []) {
    // If nothing, no need to process.
    if (empty($store_locator_ids)) {
      return [];
    }

    // Get store nids not having given locator ids.
    $store_nids = $this->database->select('node__field_store_locator_id', 'n')
      ->fields('n', ['entity_id'])
      ->condition('n.bundle', 'store')
      ->condition('n.field_store_locator_id_value', $store_locator_ids, 'NOT IN')
      ->execute()->fetchCol();

    return $store_nids;
  }

  /**
   * Wrapper to get store address based on DM version.
   *
   * @param \Drupal\node\NodeInterface $store
   *   Store node.
   * @param bool $plain_text
   *   Return format.
   * @param bool $default_lang
   *   If true, return address in english.
   *
   * @return string
   *   Rendered string.
   */
  public function getStoreAddress(NodeInterface $store, $plain_text = FALSE, $default_lang = FALSE) {
    $address = [];

    if ($this->addressBookManager->getDmVersion() == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      $store_address = $store->get('field_address')->getValue();

      if ($store_address) {
        // This conversions are required to ensure we populate term names
        // and process it properly before using in template.
        $store_address = $this->addressBookManager->getMagentoAddressFromAddressArray(reset($store_address));
        $store_address = $this->addressBookManager->getAddressArrayFromMagentoAddress($store_address, $default_lang);
        $address = [
          '#theme' => 'store_address',
          '#address' => $store_address,
        ];
      }
    }
    else {
      $address = [
        '#markup' => $store->get('field_store_address')->getString(),
      ];
    }
    if ($plain_text == FALSE) {
      return $address ? render($address) : '';
    }
    else {
      return $address ? $address['#address'] : '';
    }
  }

  /**
   * Wrapper function to get store options array.
   *
   * @param string $langcode
   *   Language in which we want the display value.
   *
   * @return array
   *   Array for #options with store code as key and title as value.
   */
  public function getAllStoresAsOptions(string $langcode = '') {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $static = &drupal_static(__METHOD__, []);

    if (isset($static[$langcode])) {
      return $static[$langcode];
    }

    $cid = 'store_options_' . $langcode;
    $cache = $this->cache->get($cid);
    if (!empty($cache->data)) {
      $static[$langcode] = $cache->data;
      return $static[$langcode];
    }

    $options = [];

    $stores = $this->nodeStorage->loadByProperties([
      'type' => 'store',
      'status' => NodeInterface::PUBLISHED,
    ]);

    /** @var \Drupal\node\NodeInterface $store */
    foreach ($stores as $store) {
      if ($store->language()->getId() != $langcode && $store->hasTranslation($langcode)) {
        $store = $store->getTranslation($langcode);
      }

      $options[$store->get('field_store_locator_id')->getString()] = $store->label();
    }

    $this->cache->set($cid, $options, Cache::PERMANENT, ['node_type:store']);

    $static[$langcode] = $options;
    return $options;
  }

}
