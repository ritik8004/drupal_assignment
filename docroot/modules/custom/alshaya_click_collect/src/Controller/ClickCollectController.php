<?php

namespace Drupal\alshaya_click_collect\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_click_collect\Ajax\ClickCollectStoresCommand;
use Drupal\alshaya_click_collect\Ajax\StoreDisplayFillCommand;
use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ClickCollectController.
 */
class ClickCollectController extends ControllerBase {

  /**
   * AlshayaApiWrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Stores Finder Utility service object.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
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
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * StoresFinderController constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   AlshayaApiWrapper service object.
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $stores_finder_utility
   *   Stores Finder Utility service object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity type manager.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper,
                              StoresFinderUtility $stores_finder_utility,
                              EntityRepositoryInterface $entity_repository,
                              ConfigFactoryInterface $config_factory,
                              CartStorageInterface $cart_storage,
                              EntityTypeManagerInterface $entity_manager,
                              Request $current_request) {
    $this->apiWrapper = $api_wrapper;
    $this->storesFinderUtility = $stores_finder_utility;
    $this->entityRepository = $entity_repository;
    $this->configFactory = $config_factory;
    $this->cartStorage = $cart_storage;
    $this->entityManager = $entity_manager;
    $this->logger = $this->getLogger('alshaya_click_collect');
    $this->currentRequest = $current_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_api.api'),
      $container->get('alshaya_stores_finder_transac.utility'),
      $container->get('entity.repository'),
      $container->get('config.factory'),
      $container->get('acq_cart.cart_storage'),
      $container->get('entity_type.manager'),
      $container->get('request_stack')->getCurrentRequest()
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
    if ($stores = $this->apiWrapper->getCartStores($cart_id, $lat, $lon)) {
      $config = $this->configFactory->get('alshaya_click_collect.settings');

      foreach ($stores as $index => &$store) {
        $store['rnc_available'] = (int) $store['rnc_available'];
        $store['sts_available'] = (int) $store['sts_available'];

        if ($extra_data = $this->storesFinderUtility->getStoreExtraData($store)) {
          $store = array_merge($store, $extra_data);

          if (!empty($store['rnc_available'])) {
            $store['delivery_time'] = $config->get('click_collect_rnc');
          }
        }
        else {
          // We don't display the stores which are not in our system.
          unset($stores[$index]);

          // Log into Drupal for admins to check and take required action.
          $this->logger->warning('Received a store in Cart Stores API response which is not yet available in Drupal. Store code: %store_code', [
            '%store_code' => $store['code'],
          ]);
        }
      }

      return $stores;
    }

    return [];
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
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return Ajax response with commands.
   */
  public function getCartStoresJson($cart_id, $lat = NULL, $lon = NULL) {
    // @todo: replace this with:
    // Drupal\alshaya_click_collect\Service\AlshayaClickCollect::getCartStores
    $stores = $this->getCartStores($cart_id, $lat, $lon);

    // Sort the stores first by distance and then by name.
    alshaya_master_utility_usort($stores, 'rnc_available', 'desc', 'distance', 'asc');

    $build['store_list'] = $build['map_info_window'] = '<span class="empty">' . $this->t('Sorry, No store found for your location.') . '</span>';
    if (count($stores) > 0) {
      $build['store_list'] = [
        '#theme' => 'click_collect_stores_list',
        '#title' => $this->t('Available at @count stores near', ['@count' => count($stores)]),
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

    // If there are no stores, hide 'list view' and 'map view'.
    if (count($stores) == 0) {
      $response->addCommand(new InvokeCommand('.stores-list-view', 'addClass', ['hidden-important']));
      $response->addCommand(new InvokeCommand('.stores-map-view', 'addClass', ['hidden-important']));
    }
    else {
      $response->addCommand(new InvokeCommand('.stores-list-view', 'removeClass', ['hidden-important']));
      $response->addCommand(new InvokeCommand('.stores-map-view', 'removeClass', ['hidden-important']));
    }

    return $response;
  }

  /**
   * Render selected store html.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return Ajax response with commands.
   */
  public function selectedStore() {
    // Get all the post data, which contains store information passed in ajax.
    $store = $this->currentRequest->request->all();

    $response = new AjaxResponse();

    if (empty($store)) {
      return $response;
    }

    $ship_type = !empty($store['rnc_available']) ? 'reserve_and_collect' : 'ship_to_store';

    $build['selected_store'] = [
      '#theme' => 'click_collect_selected_store',
      '#store' => $store,
    ];

    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'delivery'], ['query' => ['method' => 'cc']])->toString()));
      return $response;
    }

    $cart->setExtension('cc_selected_info', [
      'store_code' => $store['code'],
      'shipping_type' => $ship_type,
    ]);

    $response->addCommand(new HtmlCommand('#selected-store-content', $build));
    $response->addCommand(new InvokeCommand('#selected-store-wrapper', 'show'));
    $response->addCommand(new InvokeCommand('#store-finder-wrapper', 'hide'));
    $response->addCommand(new InvokeCommand('#selected-store-wrapper input[name="store_code"]', 'val', [$store['code']]));
    $response->addCommand(new InvokeCommand('#selected-store-wrapper input[name="shipping_type"]', 'val', [$ship_type]));
    $response->addCommand(new InvokeCommand('input[data-drupal-selector="edit-actions-ccnext"]', 'show'));
    $response->addCommand(new SettingsCommand(['alshaya_click_collect' => ['selected_store' => ['raw' => $store]]]));
    $response->addCommand(new InvokeCommand(NULL, 'clickCollectScrollTop', []));

    return $response;
  }

  /**
   * Map view of the selected store.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return Ajax response with commands.
   */
  public function storeMapView() {
    // Get all the post data, which contains store information passed in ajax.
    $store = $this->currentRequest->request->all();
    $build['map_info_window'] = [
      '#theme' => 'click_collect_store_info_window_list',
      '#stores' => [$store],
    ];

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#click-and-collect-map-view .geolocation-common-map-locations', $build['map_info_window']));
    $response->addCommand(new InvokeCommand('#click-and-collect-map-view .geolocation-common-map-locations', 'hide'));
    $response->addCommand(new ClickCollectStoresCommand(['raw' => [$store]]));
    return $response;
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
    $all_stores = $top_three = [];

    if (SKU::loadFromSku($sku)) {
      if ($stores = $this->getSkuStores($sku, $lat, $lon)) {
        $top_three = [];
        $top_three['#theme'] = 'pdp_click_collect_top_stores';
        $top_three['#stores'] = array_slice($stores, 0, $limit);
        $top_three['#has_more'] = count($stores) > $limit ? $this->t('Other stores nearby') : '';
        $top_three['#available_at_title'] = $this->t('Available at @count stores near', [
          '@count' => count($stores),
        ]);

        if ($top_three['#has_more']) {
          $store_form = $this->formBuilder()->getForm('\Drupal\alshaya_click_collect\Form\ClickCollectAvailableStores');
          $config = $this->configFactory->get('alshaya_click_collect.settings');
          $all_stores = [];
          $all_stores['#theme'] = 'pdp_click_collect_all_stores';
          $all_stores['#stores'] = $stores;
          $all_stores['#title'] = $config->get('pdp_click_collect_title');
          $all_stores['#subtitle'] = $config->get('pdp_click_collect_subtitle');
          $all_stores['#available_at_title'] = $this->t('Available at @count stores near', [
            '@count' => count($stores),
          ]);
          $all_stores['#store_finder_form'] = render($store_form);
          $all_stores['#help_text'] = $config->get('pdp_click_collect_help_text.value');
        }
      }
    }

    return ['top_three' => $top_three, 'all_stores' => $all_stores];
  }

  /**
   * Get Stores for particular SKU.
   *
   * @param string $sku
   *   SKU to check for stores.
   * @param float $lat
   *   User's latitude.
   * @param float $lon
   *   User's longitude.
   *
   * @return array
   *   Processed stores array.
   */
  private function getSkuStores($sku, $lat, $lon) {
    $stores = $this->apiWrapper->getProductStores($sku, $lat, $lon);

    if (empty($stores)) {
      return [];
    }

    // Start sequence from 1.
    $index = 1;

    // Add missing information to store data.
    array_walk($stores, function (&$store) use (&$index) {
      $store['rnc_available'] = (int) $store['rnc_available'];
      $store['sts_available'] = (int) $store['sts_available'];
      $store['sequence'] = $index++;

      if ($store_node = $this->storesFinderUtility->getTranslatedStoreFromCode($store['code'])) {
        $extra_data = $this->storesFinderUtility->getStoreExtraData($store, $store_node);
        $store = array_merge($store, $extra_data);
      }
    });

    // Sort the stores first by distance and then by name.
    alshaya_master_utility_usort($stores, 'rnc_available', 'desc', 'distance', 'asc');

    $config = $this->configFactory->get('alshaya_click_collect.settings');

    // Add sequence and proper delivery_time label and low stock text.
    foreach ($stores as $index => $store) {
      $stores[$index]['sequence'] = $index + 1;

      // Display sts label by default.
      $time = $store['sts_delivery_time_label'];

      // Display configured value for rnc if available.
      if ($store['rnc_available'] && $config) {
        $time = $config->get('click_collect_rnc');
      }

      $stores[$index]['delivery_time'] = $this->t('Collect from store in <em>@time</em>', ['@time' => $time]);
      $stores[$index]['low_stock_text'] = $store['low_stock'] ? $this->t('Low stock') : '';
    }

    return $stores;
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
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return Ajax response with commands.
   */
  public function getProductStoresJson($sku, $lat, $lon) {
    $data = $this->getProductStores($sku, $lat, $lon);

    $response = new AjaxResponse();
    $settings['alshaya_click_collect']['pdp'] = ['top_three' => FALSE, 'all_stores' => FALSE];
    if (!empty($data['top_three'])) {
      $settings['alshaya_click_collect']['pdp']['top_three'] = TRUE;
      $settings['alshaya_click_collect']['searchForm'] = FALSE;
      $response->addCommand(new HtmlCommand('.click-collect-top-stores', $data['top_three']));
      $response->addCommand(new InvokeCommand('.click-collect-form .store-finder-form-wrapper', 'hide'));
      $response->addCommand(new InvokeCommand('.click-collect-form .change-location', 'hide'));
      $response->addCommand(new InvokeCommand('.click-collect-form .available-store-text', 'show'));
      $response->addCommand(new HtmlCommand('.store-available-at-title', $data['top_three']['#available_at_title']));
      $response->addCommand(new InvokeCommand('.click-collect-form .available_store .change-location-link', 'show'));
      if (!empty($data['all_stores'])) {
        $settings['alshaya_click_collect']['pdp']['all_stores'] = TRUE;
        $response->addCommand(new HtmlCommand('.click-collect-all-stores', $data['all_stores']));
      }
      else {
        $response->addCommand(new HtmlCommand('.click-collect-all-stores', ''));
        $response->addCommand(new InvokeCommand('.click-collect-all-stores', 'hide'));
      }
    }
    else {
      $no_result_html = '<span class="empty-store-list">' . $this->t('Sorry, No store found for your location.') . '</span>';
      $response->addCommand(new InvokeCommand(NULL, 'clickCollectPdpNoStoresFound', [$no_result_html]));
    }

    $settings['alshaya_click_collect']['pdp']['ajax_call'] = TRUE;
    $response->addCommand(new InvokeCommand('.click-collect-form', 'show'));
    $response->addCommand(new StoreDisplayFillCommand($settings));
    $response->addCommand(new SettingsCommand($settings, TRUE), TRUE);

    return $response;
  }

}
