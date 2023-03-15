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
   * Return status of shoeAI 
   * 0 => disabled
   * 1 => enabled
   * 2 => experimental(TO DO)
   * if shopId is empty than also returns 0.
   * @return integer
   * Return 0 or 1 or 2.
   */
  public function getShoeAiStatus() {
    $state = 0;
    $alshaya_shoeai_settings = $this->configFactory->get('alshaya_shoeai.settings');
    if ($alshaya_shoeai_settings->get('shop_id') == '') {
      return $state;
    }
    $state = $alshaya_shoeai_settings->get('enable_shoeai');
    return $state;
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

}
