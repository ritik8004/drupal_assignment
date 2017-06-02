<?php

namespace Drupal\alshaya_stores_finder\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_api\AlshayaApiWrapper;
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

/**
 * Class StoresFinderController.
 */
class StoresFinderController extends ControllerBase {

  /**
   * API Wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * StoresFinderController constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper, EntityRepositoryInterface $entity_repository, ConfigFactoryInterface $config_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->entityRepository = $entity_repository;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_api.api'),
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
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3 form #edit-field-latitude-longitude-boundary-geolocation-geocoder-google-geocoding-api', 'val', ['']));
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

    // Clear value from search field.
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1 form #edit-field-latitude-longitude-boundary-geolocation-geocoder-google-geocoding-api', 'val', ['']));
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
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function getProductStores($sku, $lat, $lon) {
    $response = new AjaxResponse();

    if ($sku_entity = SKU::loadFromSku($sku)) {
      \Drupal::moduleHandler()->loadInclude('alshaya_stores_finder', 'inc', 'alshaya_stores_finder.utility');

      if ($stores = alshaya_stores_finder_get_product_stores($sku, $lat, $lon)) {
        $top_three = [];
        $top_three['#theme'] = 'pdp_click_collect_top_stores';
        $top_three['#stores'] = array_slice($stores, 0, 3);
        $top_three['#has_more'] = count($stores) > 3 ? t('Other stores nearby') : '';

        $response->addCommand(new HtmlCommand('.click-collect-top-stores', render($top_three)));
        $response->addCommand(new InvokeCommand('.click-collect-form .search-store', 'hide'));

        if ($top_three['#has_more']) {
          $config = $this->configFactory->get('alshaya_stores_finder.settings');
          $all_stores = [];
          $all_stores['#theme'] = 'pdp_click_collect_all_stores';
          $all_stores['#stores'] = $stores;
          $all_stores['#title'] = $config->get('pdp_click_collect_title');
          $all_stores['#subtitle'] = $config->get('pdp_click_collect_subtitle');
          $response->addCommand(new HtmlCommand('.click-collect-all-stores', render($all_stores)));
        }
        else {
          $response->addCommand(new HtmlCommand('.click-collect-all-stores', ''));
          $response->addCommand(new InvokeCommand('.click-collect-all-stores', 'hide'));
        }
      }
      else {
        $response->addCommand(new HtmlCommand('.click-collect-top-stores', ''));
        $response->addCommand(new InvokeCommand('.click-collect-form .search-store', 'show'));

        $response->addCommand(new HtmlCommand('.click-collect-all-stores', ''));
        $response->addCommand(new InvokeCommand('.click-collect-all-stores', 'hide'));
      }
    }

    return $response;
  }

}
