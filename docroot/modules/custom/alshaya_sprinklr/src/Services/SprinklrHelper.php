<?php

namespace Drupal\alshaya_sprinklr\Services;

use Drupal\Core\Config\ConfigFactoryInterface;

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
   * Constructor for the SprinklrHelper service.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Detects if the Sprinklr feature is enabled or not.
   *
   * @return bool
   *   Boolean TRUE if sprinklr feature is enabled and FALSE if not.
   */
  public function isSprinklrFeatureEnabled() {
    return $this->configFactory->get('alshaya_sprinklr.settings')->get('sprinklr_enabled');
  }

  /**
   * Get product's info local storage expiration time.
   *
   * @return array
   *   The list of urls allowed for sprinklr chatbot.
   */
  public function getAllowedUrlsForSprinklr() {
    $urls = $this->configFactory->get('alshaya_sprinklr.settings')->get('allowed_urls');
    // Since one url is entered per line we split the string by new line
    // character and return an array of urls.
    return preg_split('/\r\n|\r|\n/', $urls);
  }

}
