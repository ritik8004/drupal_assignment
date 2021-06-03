<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class containing general helper methods for SPC.
 */
class AlshayaSpcHelper {

  /**
   * Config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor for AlshayaSpcHelper.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Gets the commerce backend version.
   *
   * @return string
   *   The commerce backend verion.
   */
  public function getCommerceBackendVersion() {
    return $this->configFactory->get('alshaya_acm.cart_config')->get('version') ?? 1;
  }

}
