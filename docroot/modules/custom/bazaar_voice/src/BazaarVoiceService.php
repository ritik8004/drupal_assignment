<?php

namespace Drupal\bazaar_voice;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class Bazaar Voice Service.
 *
 * @package Drupal\bazaar_voice
 */
class BazaarVoiceService {

  /**
   * Bazaar voice script code.
   */
  const BAZAAR_VOICE_SCRIPT_CODE = 'https://apps.bazaarvoice.com/deployments/{{client_name}}/{{site_id}}/{{environment}}/{{locale}}/bv.js';

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * BazaarVoiceService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Get Bazaar voice BV pixel script url.
   *
   * @return string|string[]
   *   BV pixel script url.
   */
  public function getBazaarvoiceDynamicScriptCode() {
    $clientName = $this->getClientName();
    $siteId = $this->getSiteId();
    $environment = $this->getEnvironment();
    $locale = $this->getLocale();
    if ($clientName && $siteId && $environment && $locale) {
      $bv_parameters = [
        '{{client_name}}',
        '{{site_id}}',
        '{{environment}}',
        '{{locale}}',
      ];
      $bv_values = [$clientName, $siteId, $environment, $locale];
      $bazaar_voice_script_code = str_replace($bv_parameters, $bv_values, self::BAZAAR_VOICE_SCRIPT_CODE);
      return $bazaar_voice_script_code;
    }
    return '';
  }

  /**
   * Get BV Client Name.
   *
   * @return string|null
   *   BV Client Name or empty.
   */
  public function getClientName() {
    $client_name = $this->configFactory->get('bazaar_voice.settings')->get('client_name');
    return (!empty($client_name)) ? $client_name : '';
  }

  /**
   * Get BV Site Id.
   *
   * @return string|null
   *   Site Id or empty.
   */
  public function getSiteId() {
    $siteId = $this->configFactory->get('bazaar_voice.settings')->get('site_id');
    return (!empty($siteId)) ? $siteId : '';
  }

  /**
   * Get BV environment.
   *
   * @return string|null
   *   Environment or empty.
   */
  public function getEnvironment() {
    $environment = $this->configFactory->get('bazaar_voice.settings')->get('environment');
    return (!empty($environment)) ? $environment : '';
  }

  /**
   * Get BV locale.
   *
   * @return string|null
   *   Locale or empty.
   */
  public function getLocale() {
    $locale = $this->configFactory->get('bazaar_voice.settings')->get('locale');
    return (!empty($locale)) ? $locale : '';
  }

}
