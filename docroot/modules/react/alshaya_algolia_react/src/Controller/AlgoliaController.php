<?php

namespace Drupal\alshaya_algolia_react\Controller;

use Drupal\alshaya_algolia_react\Plugin\Block\AlshayaAlgoliaReactAutocomplete;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfig;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;
use Drupal\alshaya_acm_product\DeliveryOptionsHelper;
use Drupal\alshaya_algolia_react\Plugin\Block\AlshayaAlgoliaReactPLP;
use Drupal\alshaya_algolia_react\Plugin\Block\AlshayaAlgoliaReactPromotion;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Customer controller to add front page.
 */
class AlgoliaController extends ControllerBase {

  /**
   * Algolia React Config Helper.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfig
   */
  protected $configHelper;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Delivery options helper.
   *
   * @var \Drupal\alshaya_acm_product\DeliveryOptionsHelper
   */
  protected $deliveryOptionsHelper;

  /**
   * AlgoliaController constructor.
   *
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfig $config_helper
   *   Algolia React Config Helper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module services.
   * @param \Drupal\alshaya_acm_product\DeliveryOptionsHelper $delivery_options_helper
   *   Delivery options helper service.
   */
  public function __construct(
    AlshayaAlgoliaReactConfig $config_helper,
    ModuleHandlerInterface $module_handler,
    DeliveryOptionsHelper $delivery_options_helper
  ) {
    $this->configHelper = $config_helper;
    $this->moduleHandler = $module_handler;
    $this->deliveryOptionsHelper = $delivery_options_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config'),
      $container->get('module_handler'),
      $container->get('alshaya_acm_product.delivery_options_helper'),
    );
  }

  /**
   * Returns the build for home page.
   *
   * @return array
   *   Build array.
   */
  public function search() {
    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-search-page"></div>',
      '#attached' => [
        'drupalSettings' => [
          'algoliaSearch' => [
            'showSearchResults' => TRUE,
          ],
        ],
      ],
    ];
  }

  /**
   * Callback to get Algolia settings.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   Settings as JSON.
   */
  public function getSettings(Request $request) {
    AlshayaRequestContextManager::updateDefaultContext('app');
    $query_type = $request->query->get('type');
    $page_type = AlshayaAlgoliaReactAutocomplete::PAGE_TYPE;
    $page_sub_type = '';
    // PLP.
    if ($query_type === 'plp') {
      $page_type = AlshayaAlgoliaReactPLP::PAGE_TYPE;
      $page_sub_type = AlshayaAlgoliaReactPLP::PAGE_SUB_TYPE;
    }
    // Promotions List Page.
    elseif ($query_type === 'promotion') {
      $page_type = AlshayaAlgoliaReactPromotion::PAGE_TYPE;
      $page_sub_type = AlshayaAlgoliaReactPromotion::PAGE_SUB_TYPE;
    }
    // Brand List page.
    $this->moduleHandler->alter('algolia_react_option_list_information', $query_type, $page_type, $page_sub_type);

    $config = $this->configHelper->getAlgoliaReactCommonConfig($page_type, $page_sub_type);

    // Remmove 'gtm_key' value from sort items array.
    foreach ($config[$page_type]['filters']['sort_by']['widget']['items'] as $index => $data) {
      unset($config[$page_type]['filters']['sort_by']['widget']['items'][$index]['gtm_key']);
    }

    if (isset($config['search']['filters']['attr_delivery_ways'])) {
      if (!$this->deliveryOptionsHelper->getExpressDeliveryStatus()) {
        unset($config['search']['filters']['attr_delivery_ways']['facet_values']['express_day_delivery_available']);
      }
      if (!$this->deliveryOptionsHelper->getSameDayDeliveryStatus()) {
        unset($config['search']['filters']['attr_delivery_ways']['facet_values']['same_day_delivery_available']);
      }
    }

    $settings = [];
    $settings['application_id'] = $config['commonAlgoliaSearch']['application_id'];
    $settings['api_key'] = $config['commonAlgoliaSearch']['api_key'];
    $settings['indexName'] = $config[$page_type]['indexName'];
    $settings['filters'] = $config[$page_type]['filters'];
    $settings['gallery']['showHoverImage'] = $config['commonReactTeaserView']['gallery']['showHoverImage'];
    $settings['gallery']['showThumbnails'] = $config['commonReactTeaserView']['gallery']['showThumbnails'];
    $settings['swatches'] = $config['commonReactTeaserView']['swatches'];
    $settings['showBrandName'] = $config['commonReactTeaserView']['showBrandName'];

    return new JsonResponse($settings);
  }

}
