<?php

namespace Drupal\alshaya_stores_finder_transac\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_api\Helper\MagentoApiHelper;

/**
 * Class Alshaya Locations Controller Transac.
 */
class AlshayaLocationsTransac extends ControllerBase {

  /**
   * The lconfigfactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApi;

  /**
   * The mdc helper.
   *
   * @var \Drupal\alshaya_api\Helper\MagentoApiHelper
   */
  protected $mdcHelper;

  /**
   * AlshayaLocationsTransac constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshayaApi
   *   Config object.
   * @param \Drupal\alshaya_api\Helper\MagentoApiHelper $mdc_helper
   *   The magento api helper.
   */
  public function __construct(ConfigFactoryInterface $configFactory, AlshayaApiWrapper $alshayaApi, MagentoApiHelper $mdc_helper) {
    $this->configFactory = $configFactory;
    $this->alshayaApi = $alshayaApi;
    $this->mdcHelper = $mdc_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('alshaya_api.api'),
      $container->get('alshaya_api.mdc_helper'),
    );
  }

  /**
   * Stores list for the brand transac site.
   *
   * @return object
   *   Stores list fetched from the respective MDC API.
   */
  public function stores() {
    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('store_search'),
    ];
    $config = $this->configFactory->get('alshaya_stores_finder.settings');
    $endpoint = ltrim($config->get('filter_path'), '/');
    $result = $this->alshayaApi->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

    return new JsonResponse(json_decode($result));
  }

}
