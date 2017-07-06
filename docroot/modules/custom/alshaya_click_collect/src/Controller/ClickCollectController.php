<?php

namespace Drupal\alshaya_click_collect\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_stores_finder\StoresFinderUtility;
use Drupal\alshaya_click_collect\Form\ClickCollectAvailableStores;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ClickCollectController.
 */
class ClickCollectController extends ControllerBase {

  /**
   * Stores Finder Utility service object.
   *
   * @var \Drupal\alshaya_stores_finder\StoresFinderUtility
   */
  protected $storesFinderUtility;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The cart session.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * StoresFinderController constructor.
   *
   * @param \Drupal\alshaya_stores_finder\StoresFinderUtility $stores_finder_utility
   *   Stores Finder Utility service object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity type manager.
   */
  public function __construct(StoresFinderUtility $stores_finder_utility, EntityRepositoryInterface $entity_repository, ConfigFactoryInterface $config_factory, CartStorageInterface $cart_storage, EntityTypeManagerInterface $entity_manager) {
    $this->storesFinderUtility = $stores_finder_utility;
    $this->entityRepository = $entity_repository;
    $this->configFactory = $config_factory;
    $this->cartStorage = $cart_storage;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_stores_finder.utility'),
      $container->get('entity.repository'),
      $container->get('config.factory'),
      $container->get('acq_cart.cart_storage'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Function to get the cart stores.
   *
   * @param int $cart_id
   *   The cart id.
   * @param float $lat
   *   The latitude.
   * @param float $lon
   *   The longitude.
   *
   * @return array
   *   Return the array of all available stores.
   */
  public function getCartStores($cart_id, $lat = NULL, $lon = NULL) {

    // Get the stores from Magento.
    $api_wrapper = \Drupal::service('alshaya_api.api');
    $stores = $api_wrapper->getCartStores($cart_id, $lat, $lon);

    $config = $this->configFactory->get('alshaya_click_collect.settings');
    // Add missing information to store data.
    array_walk($stores, function (&$store) use ($config) {
      $store_utility = \Drupal::service('alshaya_stores_finder.utility');
      $extra_data = $store_utility->getStoreExtraData($store);
      if (!empty($extra_data)) {
        $store = array_merge($store, $extra_data);
        if (!empty($store['sts_available'])) {
          $store['delivery_time'] = $config->get('click_collect_sts');
        }
        elseif (!empty($store['rnc_available'])) {
          $store['delivery_time'] = $config->get('click_collect_rnc');
        }
      }
      else {
        $store['name'] = $store['code'];
        $store['address'] = $store['code'];
        $store['opening_hours'] = $store['code'];
      }
    });

    return $stores;
  }

  /**
   * Function to get the cart stores.
   *
   * @param int $cart_id
   *   The cart id.
   * @param float $lat
   *   The latitude.
   * @param float $lon
   *   The longitude.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return json response to use in jquery ajax.
   */
  public function getCartStoresJson($cart_id, $lat = NULL, $lon = NULL) {
    $stores = $this->getCartStores($cart_id, $lat, $lon);

    $list = $map = t('Sorry, No store found for selected location.');
    if (count($stores) > 0) {
      $build = [
        '#theme' => 'click_collect_stores_list',
        '#title' => t('Available at @count stores near', ['@count' => count($stores)]),
        '#stores' => $stores,
      ];
      $list = render($build);

      $build = [
        '#theme' => 'click_collect_store_info_window_list',
        '#stores' => $stores,
      ];
      $map = render($build);
    }

    return new JsonResponse([
      'output' => $list,
      'raw' => $stores,
      'mapList' => $map,
    ]);
  }

  /**
   * Render selected store html.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Output the rendered html with selected store information.
   */
  public function selectedStore() {
    // Get all the post data, which contains store information passed in ajax.
    $store = \Drupal::request()->request->all();
    $output = t("There's no store selected.");
    if (!empty($store)) {
      $elements['store_code'] = [
        '#type' => 'hidden',
        '#name' => 'store_code',
        '#value' => $store['code'],
      ];

      $ship_type = '';
      if (!empty($store['sts_available'])) {
        $ship_type = 'ship_to_store';
      }
      elseif (!empty($store['rnc_available'])) {
        $ship_type = 'reach_and_collect';
      }

      $elements['click_and_collect_type'] = [
        '#type' => 'hidden',
        '#name' => 'click_and_collect_type',
        '#value' => $ship_type,
      ];

      $elements['contact_number'] = [
        '#type' => 'mobile_number',
        '#name' => 'contact number',
        '#value' => '',
      ];

      $build = [
        '#theme' => 'click_collect_selected_store',
        '#store' => $store,
        '#fields' => render($elements),
      ];
      $output = render($build);
    }

    return new JsonResponse(['output' => render($build), 'raw' => $store]);
  }

  /**
   * Map view of the selected store.
   */
  public function storeMapView() {
    return new JsonResponse(['output' => '']);
  }

  /**
   * Get stores for a product near user's location.
   *
   * @param string $sku
   *   SKU to check for stores.
   * @param float $lat
   *   User's latitude.
   * @param float $lon
   *   User's longitude.
   * @param int $limit
   *   Limit for top stores.
   *
   * @return array
   *   Return array of top tree and all stores.
   */
  public function getProductStores($sku, $lat, $lon, $limit = 3) {
    $final_all_stores = $final_top_three = '';
    if ($sku_entity = SKU::loadFromSku($sku)) {
      if ($stores = $this->storesFinderUtility->getSkuStores($sku, $lat, $lon)) {
        $top_three = [];
        $top_three['#theme'] = 'pdp_click_collect_top_stores';
        $top_three['#stores'] = array_slice($stores, 0, $limit);
        $top_three['#has_more'] = count($stores) > $limit ? t('Other stores nearby') : '';

        if ($top_three['#has_more']) {
          $store_form = \Drupal::formBuilder()->getForm(ClickCollectAvailableStores::class);
          $config = $this->configFactory->get('alshaya_click_collect.settings');
          $all_stores = [];
          $all_stores['#theme'] = 'pdp_click_collect_all_stores';
          $all_stores['#stores'] = $stores;
          $all_stores['#title'] = $config->get('pdp_click_collect_title');
          $all_stores['#subtitle'] = $config->get('pdp_click_collect_subtitle');
          $all_stores['#store_finder_form'] = render($store_form);
        }
      }
    }

    return ['top_three' => $top_three, 'all_stores' => $all_stores];
  }

  /**
   * Get Json output of stores for a product near user's location.
   *
   * @param string $sku
   *   SKU to check for stores.
   * @param float $lat
   *   User's latitude.
   * @param float $lon
   *   User's longitude.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return json response to use in jquery ajax.
   */
  public function getProductStoresJson($sku, $lat, $lon) {
    $data = $this->getProductStores($sku, $lat, $lon);
    return new JsonResponse(['top_three' => render($data['top_three']), 'all_stores' => render($data['all_stores'])]);
  }

}
