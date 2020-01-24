<?php

namespace Drupal\dynamic_yield;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class DynamicYieldService.
 *
 * @package Drupal\dynamic_yield
 */
class DynamicYieldService {

  /**
   * Dynamic yield dynamic base code.
   */
  const DYNAMIC_YEILD_DYNAMIC_SCRIPT_CODE = '//cdn-eu.dynamicyield.com/api/{{site_id}}/api_dynamic.js';

  /**
   * Dynamic yield static base code.
   */
  const DYNAMIC_YEILD_STATIC_SCRIPT_CODE = '//cdn-eu.dynamicyield.com/api/{{site_id}}/api_static.js';

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * DynamicYieldService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicYieldDynamicScriptCode() {
    $siteId = $this->getSiteId();
    if ($siteId) {
      $dynamic_yield_dynamic_script_code = str_replace('{{site_id}}', $siteId, self::DYNAMIC_YEILD_DYNAMIC_SCRIPT_CODE);
      return $dynamic_yield_dynamic_script_code;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicYieldStaticScriptCode() {
    $siteId = $this->getSiteId();
    if ($siteId) {
      $dynamic_yield_static_script_code = str_replace('{{site_id}}', $siteId, self::DYNAMIC_YEILD_STATIC_SCRIPT_CODE);
      return $dynamic_yield_static_script_code;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSiteId() {
    $siteId = $this->configFactory->get('dynamic_yield.settings')->get('site_id');
    return (isset($siteId) && !empty($siteId)) ? $siteId : FALSE;
  }

}
