<?php

namespace Drupal\alshaya_stores_finder\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_stores_finder\Form\StoreFinderAvailableStores;
use Drupal\alshaya_stores_finder\StoresFinderUtility;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class StoresFinderController.
 */
class StoresFinderController extends ControllerBase {

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
   * StoresFinderController constructor.
   *
   * @param \Drupal\alshaya_stores_finder\StoresFinderUtility $stores_finder_utility
   *   Stores Finder Utility service object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(StoresFinderUtility $stores_finder_utility, EntityRepositoryInterface $entity_repository, ConfigFactoryInterface $config_factory) {
    $this->storesFinderUtility = $stores_finder_utility;
    $this->entityRepository = $entity_repository;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_stores_finder.utility'),
      $container->get('entity.repository'),
      $container->get('config.factory')
    );
  }

  /**
   * Ajax request on store finder map view.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Node object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function updateGlossaryView(EntityInterface $node) {
    $response = new AjaxResponse();
    $list_view = views_embed_view('stores_finder', 'page_1');
    $response->addCommand(new HtmlCommand('.view-display-id-page_2', $list_view));
    // Firing click event.
    $response->addCommand(new InvokeCommand('#row-' . $node->id(), 'trigger', ['click']));
    // Adding class for selection.
    $response->addCommand(new InvokeCommand('.row-' . $node->id(), 'addClass', ['selected']));
    // Hide the map view exposed filter.
    $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-3', ['display' => 'none']));
    // Show the list view exposed filter.
    $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-1', ['display' => 'block']));
    // Remove class.
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3', 'removeClass', ['list-view-exposed']));
    // Add class.
    $response->addCommand(new InvokeCommand('.list-view-link', 'addClass', ['active']));

    // Add class.
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1', 'addClass', ['current-view']));
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3', 'removeClass', ['current-view']));

    $response->addCommand(new InvokeCommand('body', 'removeClass', ['store-finder-view']));

    return $response;

  }

  /**
   * Toggle the view type based on the display.
   *
   * @param string $view_type
   *   The of view.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function toggleView($view_type = 'list_view') {
    $response = new AjaxResponse();
    $display = 'page_1';
    if ($view_type == 'map_view') {
      $display = 'page_3';
      $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-1', ['display' => 'none']));
      $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-3', ['display' => 'block']));
      $response->addCommand(new InvokeCommand('.map-view-link', 'addClass', ['active']));
      $response->addCommand(new InvokeCommand('.list-view-link', 'removeClass', ['active']));
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1', 'removeClass', ['current-view']));
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3', 'addClass', ['current-view']));
      // Remove store title from breadcrumb.
      $response->addCommand(new InvokeCommand(NULL, 'updateStoreFinderBreadcrumb'));

      // Clear value from search field.
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3 form #edit-geolocation-geocoder-google-geocoding-api', 'val', ['']));
    }
    else {
      $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-3', ['display' => 'none']));
      $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-1', ['display' => 'block']));
      $response->addCommand(new InvokeCommand('.list-view-link', 'addClass', ['active']));
      $response->addCommand(new InvokeCommand('.map-view-link', 'removeClass', ['active']));
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1', 'addClass', ['current-view']));
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3', 'removeClass', ['current-view']));
      // Remove store title from breadcrumb.
      $response->addCommand(new InvokeCommand(NULL, 'updateStoreFinderBreadcrumb'));
    }
    $view = views_embed_view('stores_finder', $display);
    $response->addCommand(new HtmlCommand('.view-stores-finder:first', $view));
    $response->addCommand(new InvokeCommand('body', 'removeClass', ['store-finder-view']));
    // Removing class for mobile from store list.
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1', 'removeClass', ['mobile-store-detail']));

    // Clear value from search field.
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1 form #edit-geolocation-geocoder-google-geocoding-api', 'val', ['']));
    return $response;
  }

  /**
   * Get the store detail.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Node object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function storeDetail(EntityInterface $node) {
    // Get the correct translated version of node.
    $node = $this->entityRepository->getTranslationFromContext($node);
    $build = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node);
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('.view-stores-finder:first', $build));
    $response->addCommand(new InvokeCommand('.body', 'removeClass', ['store-finder-view']));
    // Adding class for mobile for store detail.
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1', 'addClass', ['mobile-store-detail']));

    // Add store finder title in breadcrumb.
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $store_finder_node_li = '<li><a href="' . $url . '">' . $node->getTitle() . '</a></li>';
    $response->addCommand(new AppendCommand('.block-system-breadcrumb-block ol', $store_finder_node_li));

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
    $final_all_stores = $final_top_three = '';
    if ($sku_entity = SKU::loadFromSku($sku)) {
      if ($stores = $this->storesFinderUtility->getSkuStores($sku, $lat, $lon)) {
        $top_three = [];
        $top_three['#theme'] = 'pdp_click_collect_top_stores';
        $top_three['#stores'] = array_slice($stores, 0, $limit);
        $top_three['#has_more'] = count($stores) > $limit ? t('Other stores nearby') : '';

        if ($top_three['#has_more']) {
          $store_form = \Drupal::formBuilder()->getForm(StoreFinderAvailableStores::class);
          $config = $this->configFactory->get('alshaya_stores_finder.settings');
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
