<?php

namespace Drupal\alshaya_stores_finder\Plugin\views\access;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\PermissionHandlerInterface;
use Drupal\user\Plugin\views\access\Permission;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Access plugin that provides permission-based access control.
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *   id = "stores_finder_page_status",
 *   title = @Translation("Stores Finder page status"),
 *   help = @Translation("Access will be granted to users with the specified permission string and if store finder page status is enabled.")
 * )
 */
class StoresFinderPageStatus extends Permission {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a Permission object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\user\PermissionHandlerInterface $permission_handler
   *   The permission handler.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              PermissionHandlerInterface $permission_handler,
                              ModuleHandlerInterface $module_handler,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $permission_handler, $module_handler);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('user.permissions'),
      $container->get('module_handler'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    // Currently there is an issue in Drupal Org which mentions that this access
    // result gets cached: https://www.drupal.org/project/drupal/issues/2968012.
    // So for this reason we would need to run drush crf currently to view
    // the view content again once it is disabled.
    return $account->hasPermission($this->options['perm']) && ($this->getConfig()->get('stores_finder_page_status') != 0);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(
      parent::getCacheContexts(),
      $this->getConfig()->getCacheContexts()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->getConfig()->getCacheTags()
    );
  }

  /**
   * Wrapper function to get config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Stores finder config.
   */
  protected function getConfig() {
    static $config;

    if (empty($config)) {
      $config = $this->configFactory->get('alshaya_stores_finder.settings');
    }

    return $config;
  }

}
