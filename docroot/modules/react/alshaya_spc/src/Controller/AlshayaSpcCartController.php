<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\AdminContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Alshaya Spc Cart Controller.
 */
class AlshayaSpcCartController extends ControllerBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The admin context.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SystemSettings constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The admin context.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(RequestStack $request_stack, AdminContext $admin_context, ConfigFactoryInterface $config_factory) {
    $this->request = $request_stack->getCurrentRequest();
    $this->adminContext = $admin_context;
    $this->configFactory = $config_factory;
  }

  /**
   * Instantiates a new instance of this class.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The admin context.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.admin_context'),
      $container->get('router.admin_context'),
      $container->get('config.factory')
    );
  }

  /**
   * Return cart settings.
   *
   * @return array
   *   Cart settings.
   */
  public function getSettings() {
    // Do not add settings on admin pages.
    if ($this->adminContext->isAdminRoute()) {
      return [];
    }

    // Gather settings.
    $settings = $this->readSettingsFromCode();

    // Get middleware timeouts.
    $timeouts = array_map(function($key) {
      return $key['timeout'];
    }, $settings['alshaya_backend_calls_options']['middleware']);

    // Get config.
    $cart_config = $this->configFactory->get('alshaya_spc.cart_settings');

    return [
      'url' => $settings['alshaya_api.settings']['magento_host'],
      'store' => $settings['magento_lang_prefix'][$this->getDrupalLangcode()],
      'version' => $cart_config->get('version') ?? 2,
      'checkout_settings' => $settings['alshaya_checkout_settings'],
      'timeouts' => $timeouts,
    ];
  }

  /**
   * Get drupal langcode.
   *
   * @return string
   *   Drupal langcode.
   */
  public function getDrupalLangcode() {
    return $this->request->query->get('lang', 'en');
  }

  /**
   * Read the settings from code and store in object.
   */
  protected function readSettingsFromCode() {
    // Get site environment.
    require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';

    $env = $this->getEnvironment();

    // Get host_site_code or acsf_site_name based on environment.
    if ($env === 'local') {
      // Require local_sites.php file for host site code.
      require_once DRUPAL_ROOT . '/../factory-hooks/pre-settings-php/local_sites.php';
    }
    else {
      // Require sites.inc and post-sites-php/includes.php for ACSF site_name.
      require_once DRUPAL_ROOT . '/sites/g/sites.inc';
      $host = rtrim($_SERVER['HTTP_HOST'], '.');
      $data = gardens_site_data_refresh_one($host);
      $GLOBALS['gardens_site_settings'] = $data['gardens_site_settings'];
      require_once DRUPAL_ROOT . '/../factory-hooks/post-sites-php/includes.php';
    }

    $site_country_code = alshaya_get_site_country_code($this->getSiteCode());
    require_once DRUPAL_ROOT . '/../factory-hooks/environments/mapping.php';

    $settings = alshaya_get_commerce_third_party_settings(
      $site_country_code['site_code'],
      $site_country_code['country_code'],
      $env
    );

    require DRUPAL_ROOT . '/../factory-hooks/post-settings-php/alshaya_checkout_settings.php';
    require DRUPAL_ROOT . '/../factory-hooks/pre-settings-php/alshaya_backend_calls_options.settings.php';
    require DRUPAL_ROOT . '/../factory-hooks/post-settings-php/zzz_overrides.php';

    // Store site code and country code in settings.
    $settings['alshaya_site_country_code'] = [
      'site_code' => $site_country_code['site_code'],
      'country_code' => $site_country_code['country_code'],
    ];

    return $settings;
  }

  /**
   * Wrapper function to get site code.
   *
   * @return string|null
   *   Site code if available.
   */
  public function getSiteCode() {
    // @codingStandardsIgnoreLine
    global $host_site_code, $_acsf_site_name;

    // Get host_site_code or acsf_site_name based on environment.
    return ($this->getEnvironment() === 'local')
      ? $host_site_code
      : $_acsf_site_name;
  }

  /**
   * Get current environment code.
   *
   * Removes the numerical prefix.
   *
   * @return string
   *   Environment code.
   */
  public function getEnvironment() {
    $env = alshaya_get_site_environment();

    // This is to remove `01/02` etc from env name.
    if (substr($env, 0, 1) == '0') {
      $env = substr($env, 2);
    }

    return $env;
  }

  /**
   * Get current request language.
   *
   * @return string
   *   Requested language.
   */
  public function getRequestLanguage() {
    $lang = $this->request->query->get('lang', 'en');
    return in_array($lang, ['en', 'ar']) ? $lang : 'en';
  }

}
