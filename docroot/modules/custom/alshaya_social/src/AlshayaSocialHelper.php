<?php

namespace Drupal\alshaya_social;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class AlshayaSocialHelper.
 *
 * @package Drupal\alshaya_social
 */
class AlshayaSocialHelper {
  /**
   * Config Storage service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaSocialHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config storage object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Return status of social_login.
   *
   * @return bool
   *   Return true when social login is enabled and atleast one network is
   *   available.
   */
  public function getStatus() {
    if (!$this->configFactory->get('alshaya_social.settings')->get('social_login')) {
      return FALSE;
    }

    return (bool) $this->getEnabledNetworks();
  }

  /**
   * Get all social auth networks that are avaialble to display.
   *
   * @return array
   *   Return array of all available social auth to display on frontend.
   */
  public function getSocialNetworks() {
    $auth = $this->configFactory->get('social_auth.settings')->get('auth');
    $enable_networks = array_keys($this->getEnabledNetworks());
    return array_filter($auth, function ($key) use ($enable_networks) {
      return in_array($key, $enable_networks);
    }, ARRAY_FILTER_USE_KEY);
  }

  /**
   * Get all enabled social auth networks.
   *
   * @return array
   *   Return array of plugin_id, for all enabled networks.
   */
  protected function getEnabledNetworks() {
    return array_filter($this->configFactory->get('alshaya_social.settings')->get('social_networks'));
  }

}
