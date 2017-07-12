<?php

namespace Drupal\alshaya_click_collect\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_click_collect\Ajax\ClickCollectStoresCommand;
use Drupal\alshaya_stores_finder\StoresFinderUtility;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\SettingsCommand;
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

    $build['store_list'] = $build['map_info_window'] = t('Sorry, No store found for your location.');
    if (count($stores) > 0) {
      $build['store_list'] = [
        '#theme' => 'click_collect_stores_list',
        '#title' => t('Available at @count stores near', ['@count' => count($stores)]),
        '#stores' => $stores,
      ];

      $build['map_info_window'] = [
        '#theme' => 'click_collect_store_info_window_list',
        '#stores' => $stores,
      ];
    }

    // Respond to client that the entity was saved properly.
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#click-and-collect-list-view', $build['store_list']));
    $response->addCommand(new HtmlCommand('#click-and-collect-map-view .geolocation-common-map-locations', $build['map_info_window']));
    $response->addCommand(new InvokeCommand('#click-and-collect-map-view .geolocation-common-map-locations', 'hide'));
    $response->addCommand(new ClickCollectStoresCommand(['raw' => $stores]));
    return $response;
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
      $ship_type = '';
      if (!empty($store['sts_available'])) {
        $ship_type = 'ship_to_store';
      }
      elseif (!empty($store['rnc_available'])) {
        $ship_type = 'reserve_and_collect';
      }
      $build['selected_store'] = [
        '#theme' => 'click_collect_selected_store',
        '#store' => $store,
      ];
    }

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#selected-store-content', $build));
    $response->addCommand(new InvokeCommand('#selected-store-wrapper', 'show'));
    $response->addCommand(new InvokeCommand('#store-finder-wrapper', 'hide'));
    $response->addCommand(new InvokeCommand('#selected-store-wrapper input[name="store_code"]', 'val', [$store['code']]));
    $response->addCommand(new InvokeCommand('#selected-store-wrapper input[name="shipping_type"]', 'val', [$ship_type]));
    $response->addCommand(new InvokeCommand('input[data-drupal-selector="edit-actions-ccnext"]', 'show'));
    $response->addCommand(new SettingsCommand(['alshaya_click_collect' => ['selected_store' => ['raw' => $store]]]));
    return $response;
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
          $store_form = \Drupal::formBuilder()->getForm('\Drupal\alshaya_click_collect\Form\ClickCollectAvailableStores');
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
