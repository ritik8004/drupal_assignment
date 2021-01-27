<?php

namespace Drupal\alshaya_algolia_react\Controller;

use Drupal\alshaya_algolia_react\Plugin\Block\AlshayaAlgoliaReactAutocomplete;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfig;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\alshaya_acm_product\AlshayaPromoContextManager;

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
   * AlgoliaController constructor.
   *
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfig $config_helper
   *   Algolia React Config Helper.
   */
  public function __construct(AlshayaAlgoliaReactConfig $config_helper) {
    $this->configHelper = $config_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config'),
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
  public function getSettings() {
    AlshayaPromoContextManager::updateDefaultContext('app');
    $config = $this->configHelper->getAlgoliaReactCommonConfig(AlshayaAlgoliaReactAutocomplete::PAGE_TYPE);

    $settings = [];
    $settings['application_id'] = $config['commonAlgoliaSearch']['application_id'];
    $settings['api_key'] = $config['commonAlgoliaSearch']['api_key'];
    $settings['indexName'] = $config['commonAlgoliaSearch']['indexName'];
    $settings['filters'] = $config['search']['filters'];
    $settings['gallery']['showHoverImage'] = $config['commonReactTeaserView']['gallery']['showHoverImage'];
    $settings['gallery']['showThumbnails'] = $config['commonReactTeaserView']['gallery']['showThumbnails'];
    $settings['swatches'] = $config['commonReactTeaserView']['swatches'];

    return new JsonResponse($settings);
  }

}
