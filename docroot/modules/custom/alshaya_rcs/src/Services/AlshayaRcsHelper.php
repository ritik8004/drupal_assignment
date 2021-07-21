<?php

namespace Drupal\alshaya_rcs\Services;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * General Helper service for the Ashaya RCS feature.
 */
class AlshayaRcsHelper {

  /**
   * Config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor for the AddToBagHelper service.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory
  ) {
    $this->configFactory = $config_factory;
  }

  /**
   * Detects if the RCS replacement on PDP is enabled or not.
   *
   * @return bool
   *   Whether rcs replacement on pdp is enabled or not.
   */
  public function isRcsPdpEnabled() {
    return (bool) $this->configFactory->get('alshaya_rcs.settings')->get('rcs_pdp_enabled');
  }

}
