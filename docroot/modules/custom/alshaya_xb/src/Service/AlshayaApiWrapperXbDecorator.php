<?php

namespace Drupal\alshaya_xb\Service;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_api\Helper\MagentoApiHelper;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;

/**
 * Class AlshayaApiWrapperXbDecorator decorates AlshayaApiWrapper.
 */
class AlshayaApiWrapperXbDecorator extends AlshayaApiWrapper {

  /**
   * Domain config overrides.
   *
   * @var \Drupal\alshaya_xb\Service\DomainConfigOverrides
   */
  protected $configOverrides;

  /**
   * Constructs a new AlshayaApiWrapperXbDecorator object.
   *
   * @param \Drupal\alshaya_xb\Service\DomainConfigOverrides $config_overrides
   *   Domain config overrides.
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
    DomainConfigOverrides $config_overrides,
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
    $this->configOverrides = $config_overrides;
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
    // Get domain overrides.
    $configOverrides = $this->configOverrides->getConfigByDomain();

    // Get country code from domain overrides, if not available then
    // use site level country code.
    $country_code = $configOverrides['code'] ?? strtoupper(_alshaya_custom_get_site_level_country_code());

    return $this->getCustomerAddressFormByCountryCode($country_code);
  }

}
