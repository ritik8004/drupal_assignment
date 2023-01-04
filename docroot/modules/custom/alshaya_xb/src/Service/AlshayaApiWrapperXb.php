<?php

namespace Drupal\alshaya_xb\Service;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_api\Helper\MagentoApiHelper;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;

/**
 * Class AlshayaApiWrapperXb decorates AlshayaApiWrapper.
 */
class AlshayaApiWrapperXb extends AlshayaApiWrapper {
  /**
   * Inner service AlshayaApiWrapper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $innerService;

  /**
   * Request Stack.
   *
   * @var \Drupal\Core\Http\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new AlshayaApiWrapperXb object.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api
   *   Alshaya API wrapper.
   * @param \Drupal\Core\Http\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The date time service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend object for "cache.data".
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state factory.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The filesystem service.
   * @param \Drupal\alshaya_api\Helper\MagentoApiHelper $mdc_helper
   *   The magento api helper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(
    AlshayaApiWrapper $alshaya_api,
    RequestStack $request_stack,
    LanguageManagerInterface $language_manager,
    TimeInterface $date_time,
    CacheBackendInterface $cache,
    LoggerChannelFactoryInterface $logger_factory,
    StateInterface $state,
    FileSystemInterface $fileSystem,
    MagentoApiHelper $mdc_helper,
    ModuleHandlerInterface $module_handler,
    AccountInterface $current_user,
    ConfigFactoryInterface $config_factory
  ) {
    $this->innerService = $alshaya_api;
    $this->requestStack = $request_stack;
    parent::__construct(
      $language_manager,
      $date_time,
      $cache,
      $logger_factory,
      $state,
      $fileSystem,
      $mdc_helper,
      $module_handler,
      $current_user,
      $config_factory
    );
  }

  /**
   * Function to get customer address form.
   *
   * @inerhitDoc
   */
  public function getCustomerAddressForm() {
    $country_code = strtoupper(_alshaya_custom_get_site_level_country_code());

    $domainMappings = $this->innerService->configFactory->get('alshaya_xb.settings')->get('domain_mapping');

    // Get current base url.
    $base_url = $this->requestStack->getCurrentRequest()->getHost();

    $configOverrides = [];

    foreach ($domainMappings as $domainMapping) {
      // Get domain and prefix comma separated.
      $domains = explode(',', $domainMapping['domains']);
      foreach ($domains as $domain) {
        // Check if base_url has domain or the site prefix.
        if (strstr($base_url, $domain)) {
          $configOverrides = $domainMapping;
          break 2;
        }
      }
    }

    if (isset($configOverrides['code'])) {
      $endpoint = 'deliverymatrix/address-structure/country/' . strtoupper($configOverrides['code']);
    }
    else {
      $endpoint = 'deliverymatrix/address-structure/country/' . $country_code;
    }

    $request_options = [
      'timeout' => $this->innerService->mdcHelper->getPhpTimeout('dm_structure_get'),
    ];

    $response = $this->innerService->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

    if ($response && is_string($response)) {
      $form = json_decode($response, TRUE);

      if ($form && is_array($form)) {
        return $form;
      }
    }

    return [];
  }

}
