<?php

namespace Drupal\alshaya_shoeai\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Helper class for getting shoeai config.
 *
 * @package Drupal\alshaya_shoeai
 */
class AlshayaShoeAi {

  /**
   * Constant for scale.
   */
  protected const SCALE = 'eu';

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user service object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

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
   * ShoeAi Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current account object.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              AccountProxyInterface $current_user,
                              CurrentPathStack $current_path,
                              AliasManagerInterface $alias_manager) {
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
  }

  /**
   * Returns 0 - disabled/shopId empty, 1 - enabled and 2 - experimental(TO DO).
   *
   * @return int
   *   Returns status.
   */
  public function getShoeAiStatus() {
    $alshaya_shoeai_settings = $this->configFactory->get('alshaya_shoeai.settings');
    if (empty($alshaya_shoeai_settings->get('shop_id'))) {
      return 0;
    }
    return $alshaya_shoeai_settings->get('enable_shoeai');
  }

  /**
   * Helper function to get Shoeai shopId.
   *
   * @return string
   *   Return shop Id.
   */
  public function getShoeAiShopId() {
    $alshaya_shoeai_settings = $this->configFactory->get('alshaya_shoeai.settings');
    return $alshaya_shoeai_settings->get('shop_id') ?: '';
  }

  /**
   * Helper function to get Shoeai scale.
   *
   * @return string
   *   Return shoe AI scale.
   */
  public function getShoeAiScale() {
    return self::SCALE;
  }

  /**
   * Helper function to get Shoeai zeroHash.
   *
   * @return string
   *   Return zeroHash.
   */
  public function getShoeAiZeroHash() {
    // If user is anonymous.
    $zeroHash = '';
    if ($this->currentUser->isAuthenticated() &&
      !empty($this->currentUser->getEmail())) {
      $zeroHash = md5($this->currentUser->getEmail());
    }
    return $zeroHash;
  }

  /**
   * Helper function for creating shoeAi settings.
   *
   * @return array
   *   Return array of settings when shoeai is enabled.
   */
  public function getShoeAiSettings() {
    $shoeAiSettings = [];
    $status = $this->getShoeAiStatus();
    if ($status != 0) {
      $shoeAiSettings['status'] = $status;
      $shoeAiSettings['shopId'] = $this->getShoeAiShopId();
      $shoeAiSettings['scale'] = $this->getShoeAiScale();
      $shoeAiSettings['zeroHash'] = $this->getShoeAiZeroHash();
    }
    return $shoeAiSettings;
  }

  /**
   * Function for adding shoeai drupalSettings and custom library to the page.
   */
  public function attachShoeAiLibrary(&$build) {
    // Add script for shoeai in order confirmation page.
    $shoeai_config = $this->configFactory->get('alshaya_shoeai.settings');
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'] ?? [], $shoeai_config->getCacheTags());
    $shoeai_settings = $this->getShoeAiSettings();
    if (!empty($shoeai_settings)) {
      $build['#attached']['library'][] = 'alshaya_shoeai/shoeai_js';
      $build['#attached']['drupalSettings']['shoeai'] = $shoeai_settings;
    }
  }

  /**
   * Function for returning landing page path for shoeai configuration.
   *
   * @return array
   *   Return array of landing page url or empty array.
   */
  public function getShoeaiLandingPage() {
    $shoeai_landing_page = $this->configFactory->get('alshaya_shoeai.settings');
    $landingPages = $shoeai_landing_page->get('landing_page_path');
    $path = [];
    // landing_page_path is optional we need to check if value present.
    if (!empty($landingPages)) {
      $landingPages = explode(',', $landingPages);
      if (!empty($landingPages)) {
        // Remove forward slash & space from beginning.
        foreach ($landingPages as $key => $page) {
          $path[$key] = ltrim(trim($page), '/');
        }
      }
    }
    return $path;
  }

  /**
   * Helper function for checking if current page is shoeai landing page.
   *
   * @return bool
   *   Return TRUE if current path is configured else return FALSE.
   */
  public function isShoeAiLandingPage() {
    $current_path = $this->currentPath->getPath();
    $path_alias = ltrim($this->aliasManager->getAliasByPath($current_path), '/');
    // Remove / from current_path to compare the paths.
    $path = ltrim($current_path, '/');
    $shoeAiEnabled = FALSE;
    $allowed_urls = $this->getShoeaiLandingPage();
    // If allowed URLs are not set, skip all pages.
    if (empty($allowed_urls)) {
      return $shoeAiEnabled;
    }
    // Make sure alias as well as actual(/node) url are also compared.
    if (in_array($path_alias, $allowed_urls) || in_array($path, $allowed_urls)) {
      $shoeAiEnabled = TRUE;
    }

    return $shoeAiEnabled;
  }

}
