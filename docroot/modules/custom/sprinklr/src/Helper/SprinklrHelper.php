<?php

namespace Drupal\sprinklr\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\path_alias\AliasManagerInterface;

/**
 * General Helper service for the sprinklr chatbot feature.
 */
class SprinklrHelper {

  /**
   * Config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructor for the SprinklrHelper service.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    CurrentPathStack $current_path,
    AliasManagerInterface $alias_manager
  ) {
    $this->configFactory = $config_factory;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
  }

  /**
   * Detects if the Sprinklr feature is enabled or not.
   *
   * @return bool
   *   Boolean TRUE if sprinklr feature is enabled and FALSE if not.
   */
  public function isSprinklrFeatureEnabled() {
    return $this->configFactory->get('sprinklr.settings')->get('sprinklr_enabled');
  }

  /**
   * Checks if sprinklr feature is enabled for current path or not.
   *
   * @return bool
   *   TRUE if sprinklr is enabled for the current path and
   *   FALSE if not.
   */
  public function isSprinklrEnabledOnCurrentPath() {
    $current_path = $this->currentPath->getPath();
    // Get url alias for the current page to compare with allowed urls list.
    $alias = $this->aliasManager->getAliasByPath($current_path);
    $allowed_urls = $this->configFactory->get('sprinklr.settings')->get('allowed_urls');
    if (empty($allowed_urls)) {
      return FALSE;
    }
    // Since one url is entered per line we split the string by new line
    // character and check if current path exists in this array or not.
    return in_array($alias, preg_split('/\r\n|\r|\n/', $allowed_urls));
  }

}
