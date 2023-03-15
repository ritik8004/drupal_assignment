<?php

namespace Drupal\alshaya_shoeai\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Helper class for getting shoeai config.
 *
 * @package Drupal\alshaya_shoeai
 */
class AlshayaShoeAi {

  // Contact for scale in shoeAi settings.
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
   * ShoeAi Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current account object.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              AccountProxyInterface $current_user) {
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
  }

  /**
   * Returns 0 - disabled/shopId empty, 1 - enabled and 2 - experimental(TO DO).
   *
   * @return int
   *   Returns status.
   */
  public function getShoeAiStatus() {
    $state = 0;
    $alshaya_shoeai_settings = $this->configFactory->get('alshaya_shoeai.settings');
    if (!empty($alshaya_shoeai_settings->get('shop_id'))) {
      return $state;
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

}
