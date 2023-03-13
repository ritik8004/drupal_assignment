<?php

namespace Drupal\alshaya_shoeai;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Helper class for getting shoeai config.
 *
 * @package Drupal\alshaya_shoeai
 */
class AlshayaShoeAi {

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
   * Alshaya Constructor.
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
   * Check if shoeAI is enabled or not.
   *
   * @return bool
   *   Return true when shoeai is enabled.
   */
  public function isShoeAiFeatureEnabled() {
    $shoe_ai_enabled = FALSE;
    $alshaya_shoeai_settings = $this->configFactory->get('alshaya_shoeai.settings');
    $state = $alshaya_shoeai_settings->get('enable_shoeai');
    if ($state) {
      $shoe_ai_enabled = TRUE;
    }
    return $shoe_ai_enabled;
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
    $scale = 'eu';
    return $scale;
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

}
