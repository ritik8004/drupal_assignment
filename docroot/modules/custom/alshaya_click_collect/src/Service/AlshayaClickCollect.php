<?php

namespace Drupal\alshaya_click_collect\Service;

use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya Click Collect.
 *
 * @package Drupal\alshaya_click_collect\Service
 */
class AlshayaClickCollect {

  use StringTranslationTrait;

  /**
   * Stores Finder Utility service object.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
   */
  protected $storesFinderUtility;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * AlshayaClickCollect constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $stores_finder_utility
   *   Stores Finder Utility service object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    StoresFinderUtility $stores_finder_utility,
    RequestStack $request_stack
  ) {
    $this->storesFinderUtility = $stores_finder_utility;
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack->getCurrentRequest();
  }

  /**
   * Get store info for given store code.
   *
   * @param string $store_code
   *   The store code.
   *
   * @return array
   *   Return array of store related info.
   */
  public function getStoreInfo(string $store_code) {
    if ($this->getConfig()->get('feature_status') === 'disabled') {
      return [];
    }

    $store_info = ($this->requestStack->query->get('isCollectionPoint') !== 'true')
      ? $this->storesFinderUtility->getMultipleStoresExtraData([$store_code => []], NULL, TRUE)
      : $this->storesFinderUtility->getMultipleStoresExtraData([$store_code => []]);
    $store_info['rnc_config'] = $this->getConfig()->get('click_collect_rnc');
    return $store_info;
  }

  /**
   * Wrapper function to get config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Click and collect config.
   */
  protected function getConfig() {
    static $config;

    if (empty($config)) {
      $config = $this->configFactory->get('alshaya_click_collect.settings');
    }

    return $config;
  }

}
