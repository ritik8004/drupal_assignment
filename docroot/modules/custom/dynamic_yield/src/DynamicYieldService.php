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
  const DYNAMIC_YEILD_DYNAMIC_SCRIPT_CODE = '//cdn-eu.dynamicyield.com/api/{{section_id}}/api_dynamic.js';

  /**
   * Dynamic yield static base code.
   */
  const DYNAMIC_YEILD_STATIC_SCRIPT_CODE = '//cdn-eu.dynamicyield.com/api/{{section_id}}/api_static.js';

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
   * Get Dynamic yield, dynamic script url.
   *
   * @return string|string[]
   *   Dynamic script url.
   */
  public function getDynamicYieldDynamicScriptCode() {
    $sectionId = $this->getSectionId();
    if ($sectionId) {
      $dynamic_yield_dynamic_script_code = str_replace('{{section_id}}', $sectionId, self::DYNAMIC_YEILD_DYNAMIC_SCRIPT_CODE);
      return $dynamic_yield_dynamic_script_code;
    }
    return '';
  }

  /**
   * Get Dynamic yield, static script url.
   *
   * @return string|string[]
   *   Static script url.
   */
  public function getDynamicYieldStaticScriptCode() {
    $sectionId = $this->getSectionId();
    if ($sectionId) {
      $dynamic_yield_static_script_code = str_replace('{{section_id}}', $sectionId, self::DYNAMIC_YEILD_STATIC_SCRIPT_CODE);
      return $dynamic_yield_static_script_code;
    }
    return '';
  }

  /**
   * Get DY Section ID.
   *
   * @return string|null
   *   Site Id or empty.
   */
  public function getSectionId() {
    $sectionId = $this->configFactory->get('dynamic_yield.settings')->get('section_id');
    return (!empty($sectionId)) ? $sectionId : '';
  }

}
